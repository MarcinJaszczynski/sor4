<?php

namespace App\Filament\Resources\EventTemplateResource\Widgets;

use Filament\Widgets\Widget;
use App\Models\EventTemplate;

class EventTemplateQuickStatsWidget extends Widget
{
    protected static string $view = 'filament.resources.event-template-resource.widgets.event-template-quick-stats-widget';
    public ?EventTemplate $record = null;
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
            ->wherePivot('active', true)
            ->get();

        $this->stats = [
            'duration_days' => $this->record->duration_days ?? 1,
            'total_points' => $programPoints->count(),
            'calculation_points' => $programPoints->where('include_in_calculation', true)->count(),
            'program_points' => $programPoints->where('include_in_program', true)->count(),
            'is_active' => $this->record->is_active,
            'transfer_km' => $this->record->transfer_km ?? 0,
            'program_km' => $this->record->program_km ?? 0,
        ];
    }
}
