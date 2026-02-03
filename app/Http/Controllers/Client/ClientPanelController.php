<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Participant;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;

class ClientPanelController extends Controller
{
    public function dashboard($uuid)
    {
        // 1. Sprawdź czy to UUID umowy (Organizator / Klient Indywidualny)
        $contract = Contract::where('uuid', $uuid)
            ->with(['participants', 'event.documents', 'payments'])
            ->first();

        if ($contract) {
            return view('client.panel.dashboard', [
                'contract' => $contract,
                'participant' => null, // Widok właściciela
                'is_organizer' => true,
            ]);
        }

        // 2. Sprawdź czy to UUID uczestnika (Pojedynczy członek grupy)
        $participant = Participant::where('uuid', $uuid)
            ->with(['contract.event.documents'])
            ->firstOrFail();
            
        return view('client.panel.dashboard', [
            'contract' => $participant->contract,
            'participant' => $participant, // Widok uczestnika
            'is_organizer' => false,
        ]);
    }

    public function updateParticipants(Request $request, $uuid)
    {
        // Logika mieszana: 
        // Jeśli UUID to Umowa -> Aktualizuj listę wszystkich
        // Jeśli UUID to Uczestnik -> Aktualizuj tylko tego jednego
        
        $contract = Contract::where('uuid', $uuid)->first();
        if ($contract) {
            // ... (stara logika dla właściciela)
            return $this->updateAsOrganizer($request, $contract);
        }

        $participant = Participant::where('uuid', $uuid)->firstOrFail();
        return $this->updateAsParticipant($request, $participant);
    }
    
    private function updateAsOrganizer(Request $request, Contract $contract)
    {
        $data = $request->validate([
            'participants' => 'array',
            'participants.*.first_name' => 'required|string',
            'participants.*.last_name' => 'required|string',
            'participants.*.pesel' => 'nullable|string',
            'participants.*.birth_date' => 'nullable|date',
            'participants.*.id' => 'nullable|integer',
        ]);

        if (isset($data['participants'])) {
            foreach ($data['participants'] as $pData) {
                if (!empty($pData['id'])) {
                    $participant = $contract->participants()->find($pData['id']);
                    if ($participant) $participant->update($pData);
                } else {
                    $contract->participants()->create($pData);
                }
            }
        }
        return back()->with('success', 'Lista uczestników zaktualizowana.');
    }

    private function updateAsParticipant(Request $request, Participant $participant)
    {
        $data = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'pesel' => 'nullable|string',
            'birth_date' => 'nullable|date',
        ]);
        
        $participant->update($data);
        return back()->with('success', 'Twoje dane zostały zaktualizowane.');
    }

    private function generatePdf($view, $data, $filename)
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $html = view($view, $data)->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return $dompdf->stream($filename);
    }

    public function downloadContract($uuid)
    {
        $contract = Contract::where('uuid', $uuid)->firstOrFail();
        return $this->generatePdf('client.pdf.contract', compact('contract'), 'umowa_' . $contract->contract_number . '.pdf');
    }

    public function downloadAddendum($uuid, $addendumId)
    {
        $contract = Contract::where('uuid', $uuid)->firstOrFail();
        $addendum = \App\Models\ContractAddendum::where('contract_id', $contract->id)
            ->where('id', $addendumId)
            ->firstOrFail();

        return $this->generatePdf('client.pdf.addendum', compact('addendum', 'contract'), 'aneks_' . $addendum->addendum_number . '.pdf');
    }

    public function downloadVoucher($uuid)
    {
        $contract = Contract::where('uuid', $uuid)->firstOrFail();
        
        if (!$contract->is_fully_paid) {
            abort(403, 'Voucher dostępny tylko po pełnym opłaceniu.');
        }

        return $this->generatePdf('client.pdf.voucher', compact('contract'), 'voucher_' . $contract->contract_number . '.pdf');
    }
}
