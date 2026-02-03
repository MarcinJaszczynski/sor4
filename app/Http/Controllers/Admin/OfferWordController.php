<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Event;
use App\Models\Place;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Element\Section;
use Illuminate\Support\Collection;

class OfferWordController extends Controller
{
    public function download($id)
    {
        $offer = Offer::with([
            'event.programPoints' => function($q) {
                $q->orderBy('day')->orderBy('order');
            }, 
            'event.programPoints.children', 
            'template',
            'event.startPlace'
        ])->findOrFail($id);

        $event = $offer->event;
        
        $preparedAt = now();

        Settings::setCompatibility(true);
        Settings::setOutputEscapingEnabled(true);
        if (class_exists('\\ZipArchive')) {
            Settings::setZipClass(Settings::ZIPARCHIVE);
        }

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Calibri Light');
        $phpWord->setDefaultFontSize(12);

        $sectionStyle = [
            'marginTop' => 1134,
            'marginBottom' => 1134,
            'marginLeft' => 851,
            'marginRight' => 851,
        ];
        $coverSectionStyle = $sectionStyle;
        $coverSectionStyle['vAlign'] = 'center';

        $docTitle = $offer->name ?? 'Oferta';
        $docSubtitle = $offer->event->name ?? '';
        $daysDisplay = ($event->duration_days ?? null) ? $event->duration_days . ' dni' : '—';
        $startPlaceDisplay = $event->startPlace?->name ?? '—';

        $companyLines = [
            'Organizator: Biuro Podróży RAFA',
            'Ul. Marii Konopnickiej 6, 00-491 Warszawa',
            'tel. +48 606 102 243 • rafa@bprafa.pl',
            'www.bprafa.pl • NIP 716-250-87-61 • Bank Millennium S.A. 10 1160 2202 0000 0002 0065 6958',
        ];

        // Cover Section
        $coverSection = $phpWord->addSection($coverSectionStyle);
        $this->configureWordSectionBranding($coverSection);

        $coverSection->addText('Oferta wycieczki', ['size' => 26, 'bold' => true], ['alignment' => 'center']);
        $coverSection->addText($docTitle, ['size' => 26, 'bold' => true, 'color' => 'C00000'], ['alignment' => 'center']);
        if ($docSubtitle !== '') {
            $coverSection->addText($docSubtitle, ['size' => 18, 'color' => '444444'], ['alignment' => 'center']);
        }

        $coverSection->addTextBreak(1);
        $coverTable = $coverSection->addTable([
            'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
            'cellMargin' => 120,
            'width' => 9000,
        ]);
        
        $coverDetails = [
            'Data przygotowania' => $preparedAt->format('d.m.Y'),
            'Ważna do' => $offer->valid_until ? $offer->valid_until->format('d.m.Y') : '21 dni',
            'Wyjazd z' => $startPlaceDisplay,
            'Liczba dni' => $daysDisplay,
            'Dla' => $offer->participant_count . ' osób',
            'Zamawiający' => $event->client_name ?? '—',
            'Kontakt' => $event->client_email ?? '—',
        ];

        foreach ($coverDetails as $label => $value) {
            $row = $coverTable->addRow();
            $row->addCell(3500)->addText($label . ':', ['color' => '0070C0', 'bold' => true]);
            $row->addCell(5500)->addText($value, ['bold' => true]);
        }

        $coverSection->addTextBreak(1);
        foreach ($companyLines as $line) {
            $coverSection->addText($line);
        }

        // Main Section
        $section = $phpWord->addSection($sectionStyle);
        $this->configureWordSectionBranding($section);

        // Introduction
        if ($offer->introduction) {
            $section->addText('Wstęp', ['bold' => true, 'size' => 14, 'color' => '0070C0']);
            $this->appendHtmlSnippetToSection($section, $offer->introduction);
            $section->addTextBreak(1);
        }

        // Program
        $program = $this->extractProgramForWord($event);
        if (!empty($program)) {
            $section->addText('Program wycieczki', ['bold' => true, 'size' => 14, 'color' => '0070C0']);

            foreach ($program as $block) {
                $section->addTextBreak(1);
                $section->addText($block['label'], ['bold' => true, 'color' => '0070C0']);
                foreach ($block['points'] as $point) {
                    $listRun = $section->addListItemRun(0);
                    $titleStyle = $point['bold'] ? ['bold' => true] : null;
                    $listRun->addText($point['title'], $titleStyle);

                    if (!empty($point['description'])) {
                        $listRun->addText(' – ');
                        $listRun->addText($point['description']);
                    }
                    
                    if (!empty($point['children'])) {
                        foreach ($point['children'] as $child) {
                            $childRun = $section->addListItemRun(1);
                            $childTitleStyle = $child['bold'] ? ['bold' => true] : null;
                            $childRun->addText($child['title'], $childTitleStyle);
                            if (!empty($child['description'])) {
                                $childRun->addText(' – ');
                                $childRun->addText($child['description']);
                            }
                        }
                    }
                }
            }
        }

        // Price
        $section->addTextBreak(1);
        $section->addText('Kalkulacja ceny', ['bold' => true, 'size' => 14, 'color' => 'C00000']);
        
        $table = $section->addTable([
            'borderColor' => 'cccccc',
            'borderSize' => 6,
            'cellMargin' => 80,
            'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
        ]);

        $headerRow = $table->addRow();
        $headerRow->addCell(4000)->addText('Opis', ['bold' => true, 'color' => '0070C0']);
        $headerRow->addCell(3000)->addText('Wartość', ['bold' => true, 'color' => '0070C0']);

        $row = $table->addRow();
        $row->addCell(4000)->addText('Liczba uczestników');
        $row->addCell(3000)->addText($offer->participant_count);

        if ($offer->price_per_person > 0) {
            $row = $table->addRow();
            $row->addCell(4000)->addText('Cena za osobę');
            $row->addCell(3000)->addText(number_format($offer->price_per_person, 2, ',', ' ') . ' PLN');
        }

        if ($offer->total_price > 0) {
            $row = $table->addRow();
            $row->addCell(4000)->addText('Cena całkowita');
            $row->addCell(3000)->addText(number_format($offer->total_price, 2, ',', ' ') . ' PLN');
        }

        // Price Variants
        $priceTable = [];
        
        $variants = $event->pricePerPerson()
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

        $currentQty = $offer->participant_count;
        $currentPrice = $offer->price_per_person;
        
        // Determine currency from variants or default to PLN
        $commonCurrency = 'PLN';
        if (count($priceTable) > 0) {
            $commonCurrency = $priceTable[0]['curr'];
        }

        if ($currentQty > 0 && $currentPrice > 0) {
            $priceTable = array_filter($priceTable, fn($item) => $item['qty'] != $currentQty);
            $priceTable[] = [
                'qty' => $currentQty,
                'price' => $currentPrice,
                'curr' => $commonCurrency,
                'is_current' => true
            ];
        }
        usort($priceTable, fn($a, $b) => $a['qty'] <=> $b['qty']);


        if (!empty($priceTable)) {
            $section->addTextBreak(1);
            $section->addText('Warianty cenowe', ['bold' => true, 'size' => 12, 'color' => '0070C0']);

            $table = $section->addTable([
                'borderColor' => 'cccccc',
                'borderSize' => 6,
                'cellMargin' => 80,
                'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
            ]);

            $headerRow = $table->addRow();
            $headerRow->addCell(3500)->addText('Liczba osób', ['bold' => true, 'color' => '0070C0']);
            $headerRow->addCell(3500)->addText('Cena za osobę', ['bold' => true, 'color' => '0070C0']);

            foreach ($priceTable as $rowItem) {
                $qtyLabel = $rowItem['qty'] . ' osób';
                $priceLabel = number_format($rowItem['price'], 2, ',', ' ') . ' ' . $rowItem['curr'];
                
                $row = $table->addRow();
                
                if ($rowItem['is_current']) {
                    $qtyLabel .= ' (bieżąca)';
                    $row->addCell(3500)->addText($qtyLabel, ['bold' => true]);
                    $row->addCell(3500)->addText($priceLabel, ['bold' => true, 'color' => '1a56db']);
                } else {
                    $row->addCell(3500)->addText($qtyLabel);
                    $row->addCell(3500)->addText($priceLabel);
                }
            }
        }

        // Terms/Summary
        if ($offer->summary) {
            $section->addTextBreak(1);
            $section->addText('Podsumowanie', ['bold' => true, 'size' => 14, 'color' => '0070C0']);
            $this->appendHtmlSnippetToSection($section, $offer->summary);
        }

        if ($offer->terms) {
            $section->addTextBreak(1);
            $section->addText('Warunki', ['bold' => true, 'size' => 14, 'color' => '0070C0']);
            $this->appendHtmlSnippetToSection($section, $offer->terms);
        }

        // Save
        $fileName = 'oferta_' . ($offer->id) . '_' . now()->format('Ymd_His') . '.docx';
        $tempDir = storage_path('app/tmp');
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0775, true);
        }
        $filePath = $tempDir . DIRECTORY_SEPARATOR . $fileName;
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($filePath);

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }

    private function configureWordSectionBranding(Section $section): void
    {
        $header = $section->addHeader();
        $logoPath = public_path('uploads/logo.png');
        if (file_exists($logoPath)) {
             try {
                $textRun = $header->addTextRun(['alignment' => 'center']);
                $textRun->addImage($logoPath, ['width' => 90]);
                $header->addTextBreak(3);
             } catch (\Throwable $e) {
                 $header->addText('Biuro Podróży RAFA', ['bold' => true, 'size' => 12, 'color' => '0070C0'], ['alignment' => 'center']);
             }
        } else {
            $header->addText('Biuro Podróży RAFA', ['bold' => true, 'size' => 12, 'color' => '0070C0'], ['alignment' => 'center']);
        }
        $section->addFooter()->addPreserveText('Strona {PAGE} z {NUMPAGES}', ['size' => 9], ['alignment' => 'right']);
    }

    private function appendHtmlSnippetToSection(Section $section, string $html)
    {
         \PhpOffice\PhpWord\Shared\Html::addHtml($section, $html, false, false);
    }

    private function extractProgramForWord(Event $event): array
    {
        $program = [];
        $duration = (int)($event->duration_days ?? 1);
        
        // This logic mimics FrontController::extractProgramForWord but for Event model
        $points = $event->programPoints; 

        for ($day = 1; $day <= max($duration, 1); $day++) {
            $dayPoints = $points
                ->filter(fn($point) => (int)$point->day === $day)
                ->filter(fn($point) => $point->include_in_program) // Only included points
                ->sortBy('order')
                ->map(function ($point) {
                    $title = preg_replace('/\s*-?\s*\d+:\d+h?.*$/', '', strip_tags($point->name ?? ''));
                    $bold = (bool)($point->show_title_style ?? true);
                    $descriptionAllowed = (bool)($point->show_description ?? true);
                    $description = $descriptionAllowed ? strip_tags($point->description ?? '') : ''; // Basic cleanup

                    $children = collect($point->children ?? [])
                         // Logic for children might differ on Event model compared to Template
                         // Assuming children are loaded or relation exists. 
                         // EventProgramPoint children are typically recursive.
                         ->filter(function($child) {
                             return $child->include_in_program;
                         })
                         ->sortBy('order')
                         ->map(function($child) {
                              $title = preg_replace('/\s*-?\s*\d+:\d+h?.*$/', '', strip_tags($child->name ?? ''));
                             return [
                                 'title' => $title,
                                 'bold' => $child->show_title_style ?? true,
                                 'description' => ($child->show_description ?? true) ? strip_tags($child->description ?? '') : '',
                             ];
                         });

                    return [
                        'title' => $title,
                        'bold' => $bold,
                        'description' => $description,
                        'children' => $children->values()->all(),
                    ];
                })
                ->values()
                ->all();

            if (!empty($dayPoints)) {
                $program[] = [
                    'label' => "Dzień {$day}",
                    'points' => $dayPoints,
                ];
            }
        }
        return $program;
    }
}
