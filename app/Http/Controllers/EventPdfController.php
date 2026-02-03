<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;

class EventPdfController extends Controller
{
    /**
     * Generate pilot briefing PDF
     */
    public function pilotPdf(Event $event): Response
    {
        $event->load([
            'eventTemplate',
            'programPoints.templatePoint',
            'hotelDays',
            'costs',
            'documents' => function ($query) {
                $query->where('is_visible_pilot', true);
            }
        ]);

        $html = view('reports.pilot-pdf', ['event' => $event])->render();
        
        return $this->generatePdf($html, "pilot_{$event->public_code}_{$event->name}.pdf");
    }

    /**
     * Generate driver information PDF
     */
    public function driverPdf(Event $event): Response
    {
        $event->load([
            'eventTemplate',
            'programPoints.templatePoint',
            'hotelDays',
            'documents' => function ($query) {
                $query->where('is_visible_driver', true);
            }
        ]);

        $html = view('reports.driver-pdf', ['event' => $event])->render();
        
        return $this->generatePdf($html, "driver_{$event->public_code}_{$event->name}.pdf");
    }

    /**
     * Generate hotel agenda PDF
     */
    public function hotelPdf(Event $event): Response
    {
        $event->load([
            'eventTemplate',
            'programPoints.templatePoint',
            'hotelDays',
            'documents' => function ($query) {
                $query->where('is_visible_hotel', true);
            }
        ]);

        $html = view('reports.hotel-pdf', ['event' => $event])->render();
        
        return $this->generatePdf($html, "hotel_{$event->public_code}_{$event->name}.pdf");
    }

    /**
     * Generate event briefcase PDF (full documentation)
     */
    public function briefcasePdf(Event $event): Response
    {
        $event->load([
            'eventTemplate',
            'programPoints.templatePoint',
            'hotelDays',
            'costs',
            'documents'
        ]);

        $html = view('reports.briefcase-pdf', ['event' => $event])->render();
        
        return $this->generatePdf($html, "briefcase_{$event->public_code}_{$event->name}.pdf");
    }

    /**
     * Helper method to generate PDF from HTML
     */
    protected function generatePdf(string $html, string $filename): Response
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
