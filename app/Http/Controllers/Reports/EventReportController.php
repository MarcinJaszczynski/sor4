<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;

class EventReportController extends Controller
{
    public function roomingList($eventId)
    {
        $event = Event::findOrFail($eventId);
        
        // Grupujemy uczestników według kontraktów (domyślne pokoje)
        // W przyszłości można dodać pole room_id do ręcznego grupowania
        $groupedParticipants = $event->contracts->map(function($contract) {
            return [
                'room_type' => $contract->participants->first()->room_type ?? 'DBL', // Domyślny typ z pierwszego uczestnika
                'participants' => $contract->participants,
                'notes' => $contract->participants->pluck('room_notes')->filter()->join(', ')
            ];
        });

        $pdf = $this->generatePdf('reports.rooming_list', compact('event', 'groupedParticipants'));
        return $pdf->stream('rooming_list_' . $event->id . '.pdf');
    }

    public function flightManifest($eventId)
    {
        $event = Event::findOrFail($eventId);
        
        // Pobieramy wszystkich uczestników
        $participants = $event->contracts->flatMap->participants;

        $pdf = $this->generatePdf('reports.flight_manifest', compact('event', 'participants'));
        return $pdf->stream('flight_manifest_' . $event->id . '.pdf');
    }

    private function generatePdf($view, $data)
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        $html = view($view, $data)->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape'); // Raporty zazwyczaj szerokie
        $dompdf->render();
        
        return $dompdf;
    }
}
