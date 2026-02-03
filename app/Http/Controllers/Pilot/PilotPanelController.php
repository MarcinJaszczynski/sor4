<?php

namespace App\Http\Controllers\Pilot;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPayment;
use App\Models\PaymentType;
use App\Models\Participant;
use App\Models\PilotExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PilotPanelController extends Controller
{
    public function index()
    {
        $events = Auth::user()->events()->orderBy('start_date')->get();
        return view('pilot.dashboard', compact('events'));
    }

    public function show($eventId)
    {
        $event = Auth::user()->events()->findOrFail($eventId);
        return view('pilot.event', compact('event'));
    }

    public function participants($eventId)
    {
        $event = Auth::user()->events()->findOrFail($eventId);
        
        $participants = Participant::whereHas('contract', function($q) use ($eventId) {
            $q->where('event_id', $eventId);
        })->orderBy('last_name')->get();

        return view('pilot.participants', compact('event', 'participants'));
    }

    public function updateParticipant(Request $request, $eventId, $participantId)
    {
        Auth::user()->events()->findOrFail($eventId);
        
        $participant = Participant::findOrFail($participantId);
        
        $data = $request->validate([
            'diet_info' => 'nullable|string',
            'seat_number' => 'nullable|string',
        ]);

        $participant->update($data);
        
        return back()->with('success', 'Zaktualizowano dane pasażera.');
    }

    public function expenses($eventId)
    {
        $event = Auth::user()->events()->findOrFail($eventId);
        $expenses = PilotExpense::where('event_id', $eventId)
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        $pilotPayPoints = $event->programPoints()
            ->with('templatePoint')
            ->where('pilot_pays', true)
            ->orderBy('day')
            ->orderBy('order')
            ->get();
            
        return view('pilot.expenses', compact('event', 'expenses', 'pilotPayPoints'));
    }

    public function payments($eventId)
    {
        $event = Auth::user()->events()->findOrFail($eventId);
        $payments = EventPayment::where('event_id', $eventId)
            ->where('source', 'pilot_cash')
            ->where('created_by_user_id', Auth::id())
            ->latest('payment_date')
            ->latest('id')
            ->get();

        return view('pilot.payments', compact('event', 'payments'));
    }

    public function storePayment(Request $request, $eventId)
    {
        $event = Auth::user()->events()->findOrFail($eventId);

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|max:3',
            'description' => 'nullable|string|max:255',
            'payment_date' => 'required|date',
            'documents' => 'nullable|array',
            'documents.*' => 'nullable|file|max:10240',
        ]);

        $documents = [];
        if ($request->hasFile('documents')) {
            foreach ((array) $request->file('documents') as $file) {
                if ($file) {
                    $documents[] = $file->store('event-payments-documents', 'public');
                }
            }
        }

        $cashTypeId = PaymentType::query()
            ->where('name', 'like', '%gotówka%')
            ->value('id');

        if (! $cashTypeId) {
            $cashTypeId = PaymentType::query()->create([
                'name' => 'Gotówka (pilot)',
                'description' => 'Wpłata przyjęta przez pilota na miejscu',
            ])->id;
        }

        EventPayment::create([
            'event_id' => $event->id,
            'created_by_user_id' => Auth::id(),
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'payment_date' => $data['payment_date'],
            'payment_type_id' => $cashTypeId,
            'description' => $data['description'] ?? null,
            'is_advance' => false,
            'documents' => $documents,
            'source' => 'pilot_cash',
        ]);

        return back()->with('success', 'Wpłata została zapisana.');
    }

    public function storeExpense(Request $request, $eventId)
    {
        $event = Auth::user()->events()->findOrFail($eventId);
        
        $data = $request->validate([
            'event_program_point_id' => 'nullable|integer|exists:event_program_points,id',
            'amount' => 'required|numeric',
            'currency' => 'required|string|max:3',
            'description' => 'nullable|string',
            'document_image' => 'nullable|image|max:10240',
            'expense_date' => 'required|date',
        ]);

        if (!empty($data['event_program_point_id'])) {
            $belongs = $event->programPoints()
                ->where('id', $data['event_program_point_id'])
                ->exists();
            if (! $belongs) {
                return back()->withErrors(['event_program_point_id' => 'Wybrany punkt programu nie należy do tej imprezy.']);
            }

            $isPilotPay = $event->programPoints()
                ->where('id', $data['event_program_point_id'])
                ->where('pilot_pays', true)
                ->exists();
            if (! $isPilotPay) {
                return back()->withErrors(['event_program_point_id' => 'Ten punkt programu nie jest oznaczony jako opłacany przez pilota.']);
            }
        }

        $path = null;
        if ($request->hasFile('document_image')) {
            $path = $request->file('document_image')->store('pilot_expenses', 'public');
        }

        PilotExpense::create([
            'event_id' => $event->id,
            'event_program_point_id' => $data['event_program_point_id'] ?? null,
            'user_id' => Auth::id(),
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'description' => $data['description'] ?? null,
            'expense_date' => $data['expense_date'],
            'document_image' => $path,
            'status' => 'pending', 
        ]);

        return back()->with('success', 'Wydatek dodany.');
    }
}
