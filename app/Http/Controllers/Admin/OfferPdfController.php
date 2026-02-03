<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Dompdf\Dompdf;
use Dompdf\Options;

class OfferPdfController extends Controller
{
    public function download($id)
    {
        $offer = Offer::with(['event.programPoints.children', 'items', 'template'])->findOrFail($id);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);

        // Prepare program data similar to FrontController/WordController
        $program = [];
        if ($offer->event) {
            $duration = (int)($offer->event->duration_days ?? 1);
            $points = $offer->event->programPoints;
            
            for ($day = 1; $day <= max($duration, 1); $day++) {
                $dayPoints = $points
                    ->filter(fn($point) => (int)$point->day === $day)
                    ->filter(fn($point) => $point->include_in_program)
                    ->sortBy('order')
                    ->map(function ($point) {
                         $children = collect($point->children ?? [])
                             ->filter(fn($child) => $child->include_in_program)
                             ->sortBy('order');
                        
                         return $point;
                    });
                
                if ($dayPoints->isNotEmpty()) {
                    $program[$day] = $dayPoints;
                }
            }
        }

        // Prepare price variants
        $priceTable = [];
        
        // 1. Add variants from calculation
        if ($offer->event) {
            $variants = $offer->event->pricePerPerson()
                ->with(['currency', 'eventTemplateQty'])
                ->get()
                ->filter(fn($p) => $p->price_per_person > 0 && $p->eventTemplateQty?->qty);

            foreach ($variants as $variant) {
                $priceTable[] = [
                    'qty' => $variant->eventTemplateQty->qty,
                    'price' => $variant->price_per_person,
                    'curr' => $variant->currency->code ?? 'PLN',
                    'is_current' => false,
                ];
            }
        }

        // 2. Add current offer price (if not already covered by exact qty match, generally we want to show it explicitly)
        // Check if we have exact match in variants
        $currentQty = $offer->participant_count;
        $currentPrice = $offer->price_per_person;
        
        // Determine currency from variants or default to PLN
        $commonCurrency = 'PLN';
        if (count($priceTable) > 0) {
            // Assume homogeneous currency for the event
            $commonCurrency = $priceTable[0]['curr'];
        }

        if ($currentQty > 0 && $currentPrice > 0) {
            // Remove existing entry for this qty to overwrite with "Current" offer price
            // or just append and mark is_current? 
            // Better strategy: Filter out variant with same qty, add current with is_current=true
            $priceTable = array_filter($priceTable, fn($item) => $item['qty'] != $currentQty);
            
            $priceTable[] = [
                'qty' => $currentQty,
                'price' => $currentPrice,
                'curr' => $commonCurrency,
                'is_current' => true
            ];
        }

        // 3. Sort by qty
        usort($priceTable, fn($a, $b) => $a['qty'] <=> $b['qty']);

        $viewName = $offer->template?->view_name ?? 'admin.pdf.offer';
        if (!view()->exists($viewName)) {
            $viewName = 'admin.pdf.offer';
        }

        $html = view($viewName, compact('offer', 'program', 'priceTable'))->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return $dompdf->stream('oferta_' . ($offer->name ?? $offer->id) . '.pdf');
    }
}
