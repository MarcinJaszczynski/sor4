<?php

namespace App\Filament\Resources\EventResource\Widgets;

use Filament\Widgets\Widget;
use App\Models\Event;

class EventQuickStatsWidget extends Widget
{
    protected static string $view = 'filament.resources.event-resource.widgets.event-quick-stats-widget';
    public ?Event $record = null;
    protected int | string | array $columnSpan = 'full';

    public $stats = [];

    public function mount()
    {
        if ($this->record) {
            $this->loadStats();
        }
    }

    public function loadStats()
    {
        $programPoints = $this->record->programPoints()
            ->where('active', true)
            ->get();

        $engine = new \App\Services\EventCalculationEngine();
        $calculation = $engine->calculate($this->record);

        $this->stats = [
            'participant_count' => $this->record->participant_count ?? 0,
            'total_points' => $programPoints->count(),
            'calculation_points' => $programPoints->where('include_in_calculation', true)->count(),
            'program_points' => $programPoints->where('include_in_program', true)->count(),
            'program_cost' => $calculation['program_cost'],
            'transport_cost' => $calculation['transport_cost'],
            'total_cost' => $calculation['total_cost'],
            'cost_per_person' => $calculation['cost_per_person'],
            'duration_days' => $this->record->duration_days,
            'status' => $this->record->status,
        ];
    }
}
