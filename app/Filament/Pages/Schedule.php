<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Event;
use App\Models\Bus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Schedule extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Grafik (Gantt)';
    protected static ?string $title = 'Grafik Zajętości';
    protected static string $view = 'filament.pages.schedule';
    protected static ?int $navigationSort = 20;

    public $month;
    public $year;

    public function mount()
    {
        $this->month = Carbon::now()->month;
        $this->year = Carbon::now()->year;
    }

    public function getViewData(): array
    {
        return $this->getScheduleData();
    }

    protected function getScheduleData(): array
    {
        $startOfMonth = Carbon::createFromDate($this->year, $this->month, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();
        $daysInMonth = $startOfMonth->daysInMonth;

        // Fetch Buses with Events in range
        $buses = Bus::all()->map(function ($bus) use ($startOfMonth, $endOfMonth) {
            // Use COALESCE(end_date, start_date) so events without end_date are treated as single-day events
            $bus->events_in_range = Event::where('bus_id', $bus->id)
                ->whereNotNull('start_date')
                ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                      ->orWhereBetween(DB::raw('COALESCE(end_date, start_date)'), [$startOfMonth, $endOfMonth])
                      ->orWhere(function ($sq) use ($startOfMonth, $endOfMonth) {
                          $sq->where('start_date', '<', $startOfMonth)
                             ->whereRaw('COALESCE(end_date, start_date) > ?', [$endOfMonth]);
                      });
                })->get();
            
            $bus->conflicts = $this->detectConflicts($bus->events_in_range);
            return $bus;
        });

        // Fetch Pilots (Users who are assigned to at least one event or have role pilot)
        // Check for specific role if available, otherwise get all users who have events
                $pilots = User::whereHas('events', function($q) use ($startOfMonth, $endOfMonth) {
                         $q->whereNotNull('start_date')
                             ->where(function ($q2) use ($startOfMonth, $endOfMonth) {
                                        $q2->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                                            ->orWhereBetween(DB::raw('COALESCE(end_date, start_date)'), [$startOfMonth, $endOfMonth])
                                            ->orWhere(function ($sq) use ($startOfMonth, $endOfMonth) {
                                                    $sq->where('start_date', '<', $startOfMonth)
                                                         ->whereRaw('COALESCE(end_date, start_date) > ?', [$endOfMonth]);
                                            });
                                });
        })->get();

        foreach($pilots as $pilot) {
            // Use same COALESCE logic as for buses so events without end_date are included
            $pilot->events_in_range = $pilot->events()
                ->whereNotNull('start_date')
                ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                        ->orWhereBetween(DB::raw('COALESCE(end_date, start_date)'), [$startOfMonth, $endOfMonth])
                        ->orWhere(function ($sq) use ($startOfMonth, $endOfMonth) {
                            $sq->where('start_date', '<', $startOfMonth)
                                ->whereRaw('COALESCE(end_date, start_date) > ?', [$endOfMonth]);
                        });
                })->get();
            $pilot->conflicts = $this->detectConflicts($pilot->events_in_range);
        }

        return [
            'buses' => $buses,
            'pilots' => $pilots,
            'daysInMonth' => $daysInMonth,
            'currentDate' => Carbon::createFromDate($this->year, $this->month, 1),
        ];
    }

    public function downloadPdf()
    {
        $data = $this->getScheduleData();
        
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml(view('pdf.schedule', $data)->render());
        $dompdf->setPaper('A3', 'landscape');
        $dompdf->render();

        return response()->streamDownload(
            fn () => print($dompdf->output()),
            'grafik-' . $this->month . '-' . $this->year . '.pdf'
        );
    }
    
    private function detectConflicts($events)
    {
        $conflicts = [];
        foreach ($events as $eventA) {
            foreach ($events as $eventB) {
                if ($eventA->id === $eventB->id) continue;

                $aStart = $eventA->start_date;
                $aEnd = $eventA->end_date ?? $eventA->start_date;
                $bStart = $eventB->start_date;
                $bEnd = $eventB->end_date ?? $eventB->start_date;

                // Check overlap
                if ($aStart <= $bEnd && $aEnd >= $bStart) {
                    $conflicts[] = $eventA->id;
                }
            }
        }
        return array_unique($conflicts);
    }
    
    public function nextMonth()
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1)->addMonth();
        $this->month = $date->month;
        $this->year = $date->year;
    }
    
    public function prevMonth()
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1)->subMonth();
        $this->month = $date->month;
        $this->year = $date->year;
    }

    public function currentMonth()
    {
        $this->month = Carbon::now()->month;
        $this->year = Carbon::now()->year;
    }
}
