<?php

namespace App\Services;

use App\Models\Event;
use App\Models\ContractTemplate;
use Illuminate\Support\Str;

class ContractGeneratorService
{
    public function generate(Event $event, ContractTemplate $template): string
    {
        $content = $template->content;
        
        // Basic Event Data
        $content = str_replace('[impreza_nazwa]', $event->name, $content);
        $startDate = $event->start_date ? $event->start_date->format('d.m.Y') : '---';
        $endDate = $event->end_date ? $event->end_date->format('d.m.Y') : '---';
        $content = str_replace('[termin]', $startDate . ' - ' . $endDate, $content);
        $content = str_replace('[liczba_osob]', $event->participant_count ?? 0, $content);
        
        // Client Data
        $content = str_replace('[klient_nazwa]', $event->client_name ?? '....................', $content);
        $content = str_replace('[klient_email]', $event->client_email ?? '....................', $content);
        $content = str_replace('[klient_telefon]', $event->client_phone ?? '....................', $content);
        
        // Financials
        $totalCost = $event->total_cost ?? 0;
        $content = str_replace('[cena_calkowita]', number_format($totalCost, 2) . ' PLN', $content);
        $costPerPerson = ($event->participant_count && $event->participant_count > 0) ? $totalCost / $event->participant_count : 0;
        $content = str_replace('[cena_osoba]', number_format($costPerPerson, 2) . ' PLN', $content);
        
        // Program Table
        if (Str::contains($content, '[program]')) {
            $programHtml = $this->generateProgramHtml($event);
            $content = str_replace('[program]', $programHtml, $content);
        }
        
        // Calculation Table (Variants)
        if (Str::contains($content, '[kalkulacja]')) {
            $calcHtml = $this->generateCalculationHtml($event);
            $content = str_replace('[kalkulacja]', $calcHtml, $content);
        }

        return $content;
    }

    private function generateProgramHtml(Event $event): string
    {
        $points = $event->programPoints()
            ->where('active', true)
            ->where('include_in_program', true)
            ->orderBy('day')
            ->orderBy('order')
            ->get();
            
        $html = '<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">';
        $html .= '<thead><tr style="background-color: #f3f4f6;"><th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Dzień</th><th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Opis</th></tr></thead>';
        $html .= '<tbody>';
        
        foreach ($points as $point) {
            $html .= '<tr>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px; width: 10%; text-align: center;">' . $point->day . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px;">';
            $html .= '<strong>' . ($point->templatePoint->name ?? '') . '</strong>';
            if ($point->description && $point->show_description) {
                $html .= '<br><span style="font-size: 0.9em; color: #555;">' . $point->description . '</span>';
            }
            $html .= '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';
        return $html;
    }

    private function generateCalculationHtml(Event $event): string
    {
        // Get stored prices
        $prices = $event->pricePerPerson()->get();
        
        if ($prices->isEmpty()) {
            return '<p><em>Brak zapisanych wariantów cenowych.</em></p>';
        }
        
        $html = '<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">';
        $html .= '<thead><tr style="background-color: #f3f4f6;">
            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Wariant</th>
            <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Cena/os</th>
            <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Cena całkowita</th>
        </tr></thead>';
        $html .= '<tbody>';
        
        foreach ($prices as $price) {
            $label = $price->event_template_qty_id ? 'Wariant szablonu' : ($price->qty ? $price->qty . ' osób' : 'Domyślny');
            
            $html .= '<tr>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $label . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: right;">' . number_format($price->price_per_person, 2) . ' PLN</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: right;">' . number_format($price->price_with_tax, 2) . ' PLN</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';
        return $html;
    }
}
