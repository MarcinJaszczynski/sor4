<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventDocument;
use App\Models\EventPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

class PortalController extends Controller
{
    public function showLogin()
    {
        if (session()->has('portal_event_id')) {
             return redirect()->route('portal.dashboard');
        }
        return view('portal.login');
    }

    public function login(Request $request)
    {
        // Support legacy access-code login via the same endpoint used by tests
        if ($request->has('access_code')) {
            $code = trim($request->input('access_code'));

            $eventForManager = Event::where('access_code_manager', $code)->first();
            if ($eventForManager) {
                session([
                    'portal_event_id' => $eventForManager->id,
                    'portal_role' => 'manager'
                ]);
                return redirect()->route('portal.dashboard');
            }

            $eventForParticipant = Event::where('access_code_participant', $code)->first();
            if ($eventForParticipant) {
                session([
                    'portal_event_id' => $eventForParticipant->id,
                    'portal_role' => 'participant'
                ]);
                return redirect()->route('portal.dashboard');
            }

            return back()->with('error', 'Nieprawidłowy kod dostępu.');
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && \Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            
            $latestPivot = \Illuminate\Support\Facades\DB::table('event_user')
                ->where('user_id', $user->id)
                ->latest('created_at')
                ->first();

            if ($latestPivot) {
                // If the user has multiple events, we take the active one or latest.
                $role = $latestPivot->role;
                $eventId = $latestPivot->event_id;

                session([
                    'portal_event_id' => $eventId,
                    'portal_role' => $role,
                    'portal_user_id' => $user->id,
                ]);

                return redirect()->route('portal.dashboard');
            } else {
                 return back()->with('error', 'Brak przypisanych wycieczek dla tego konta.');
            }
        }

        return back()->with('error', 'Nieprawidłowy login lub hasło.');
    }

    public function logout()
    {
        session()->forget(['portal_event_id', 'portal_role', 'portal_user_id']);
        return redirect()->route('portal.login');
    }

    public function validateCode(Request $request)
    {
        $request->validate(['access_code' => 'required|string']);
        $code = trim($request->input('access_code'));

        $eventForManager = Event::where('access_code_manager', $code)->first();
        if ($eventForManager) {
            session([
                'activation_event_id' => $eventForManager->id,
                'activation_role' => 'manager'
            ]);
            return redirect()->route('portal.register');
        }

        $eventForParticipant = Event::where('access_code_participant', $code)->first();
        if ($eventForParticipant) {
            session([
                'activation_event_id' => $eventForParticipant->id,
                'activation_role' => 'participant'
            ]);
            return redirect()->route('portal.register');
        }

        return back()->with('error', 'Nieprawidłowy kod dostępu.');
    }

    public function showRegister()
    {
        if (!session()->has('activation_event_id')) {
            return redirect()->route('portal.login');
        }
        $event = Event::find(session('activation_event_id'));
        if (!$event) return redirect()->route('portal.login');

        return view('portal.register', compact('event'));
    }

    public function register(Request $request)
    {
        if (!session()->has('activation_event_id')) {
            return redirect()->route('portal.login');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'type' => 'client',
            'status' => 'active',
        ]);

        // Attach to Event
        \Illuminate\Support\Facades\DB::table('event_user')->insert([
            'event_id' => session('activation_event_id'),
            'user_id' => $user->id,
            'role' => session('activation_role'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Login (Portal Session)
        session([
            'portal_event_id' => session('activation_event_id'),
            'portal_role' => session('activation_role'),
            'portal_user_id' => $user->id,
        ]);
        
        session()->forget(['activation_event_id', 'activation_role']);

        return redirect()->route('portal.dashboard')->with('success', 'Konto zostało utworzone. Witamy!');
    }

    public function dashboard()
    {
        $event = Event::with(['programPoints' => function ($q) {
            $q->where('include_in_program', true)->orderBy('day')->orderBy('order');
        }])->findOrFail(session('portal_event_id'));
        
        $role = session('portal_role');

        return view('portal.dashboard', compact('event', 'role'));
    }

    public function documents()
    {
        $event = Event::findOrFail(session('portal_event_id'));
        $role = session('portal_role');
        
        $documents = EventDocument::where('event_id', $event->id)
            ->when($role !== 'manager', function ($q) {
                $q->where('type', 'public');
            })
            ->get();

        return view('portal.documents', compact('event', 'documents', 'role'));
    }

    public function downloadDocument($id)
    {
        $document = EventDocument::findOrFail($id);
        if ($document->event_id != session('portal_event_id')) {
            abort(403);
        }
        
        if (session('portal_role') !== 'manager' && $document->type !== 'public') {
            abort(403);
        }

        return Storage::download($document->file_path, $document->name);
    }

    public function payments()
    {
        $event = Event::findOrFail(session('portal_event_id'));
        $role = session('portal_role');

        if ($role === 'manager') {
            $payments = EventPayment::where('event_id', $event->id)->latest()->get();
        } else {
            // Uczestnik widzi tylko formularz płatności, ew. historię jeśli zrobilibyśmy system Userów
            // Ale skoro to "bramka", to widzi "Dokonaj płatności".
            $payments = collect(); 
        }

        return view('portal.payments', compact('event', 'payments', 'role'));
    }

    public function processPayment(Request $request)
    {
        // Symulacja
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payer_name' => 'required|string',
        ]);

        // Zapisz wpłatę
        EventPayment::create([
            'event_id' => session('portal_event_id'),
            'amount' => $request->amount,
            'currency' => 'PLN',
            // 'payer_name' => $request->payer_name, // Musiałbym dodać do bazy, na razie description
            'description' => 'Wpłata online od: ' . $request->payer_name,
            'payment_date' => now(),
            'is_advance' => false,
            'source' => 'online',
        ]);

        return back()->with('success', 'Płatność przyjęta pomyślnie (Symulacja).');
    }

    public function contact()
    {
        $event = Event::findOrFail(session('portal_event_id'));
        $role = session('portal_role');
        return view('portal.contact', compact('event', 'role'));
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'message' => 'required|string',
        ]);

        // Tu można wysłać e-mail lub zapisać w bazie
        // Zapiszmy w logach dla symulacji
        \Log::info('Wiadomość z portalu: ' . $request->message . ' od ' . $request->name . ' (' . $request->email . ')');

        return back()->with('success', 'Wiadomość została wysłana.');
    }

    public function expenses()
    {
        $event = Event::findOrFail(session('portal_event_id'));
        $role = session('portal_role');

        if ($role !== 'pilot' && $role !== 'manager') {
            abort(403);
        }

        $expenses = \App\Models\PilotExpense::where('event_id', $event->id)
            ->when($role === 'pilot', function($q) {
                $q->where('user_id', session('portal_user_id'));
            })
            ->latest()
            ->get();

        return view('portal.expenses', compact('event', 'role', 'expenses'));
    }

    public function storeExpense(Request $request)
    {
        $role = session('portal_role');
        if ($role !== 'pilot' && $role !== 'manager') {
            abort(403);
        }

        $request->validate([
            'amount' => 'required|numeric',
            'currency' => 'required|string|max:10',
            'description' => 'required|string',
            'expense_date' => 'required|date',
            'document' => 'nullable|image|max:5120', // max 5MB
        ]);

        $imagePath = null;
        if ($request->hasFile('document')) {
            $imagePath = $request->file('document')->store('pilot-expenses', 'public');
        }

        \App\Models\PilotExpense::create([
            'event_id' => session('portal_event_id'),
            'user_id' => session('portal_user_id'),
            'amount' => $request->amount,
            'currency' => $request->currency,
            'description' => $request->description,
            'expense_date' => $request->expense_date,
            'document_image' => $imagePath,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Wydatek został zapisany.');
    }

    public function pilotMessages()
    {
        $event = Event::findOrFail(session('portal_event_id'));
        $role = session('portal_role');

        // Pokaż e-maile powiązane z tą imprezą
        $messages = $event->emails()->latest('date')->get();

        return view('portal.messages', compact('event', 'role', 'messages'));
    }
}
