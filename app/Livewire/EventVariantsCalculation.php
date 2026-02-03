<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Event;
use App\Services\EventCalculationEngine;

class EventVariantsCalculation extends Component
{
    public Event $record;
    public $variants = [];
    public $calculationResults = [];

    public function mount(Event $ownerRecord)
    {
        $this->record = $ownerRecord;
        
        // Default variant = current event config
        $this->variants = [
            [
                'qty' => $this->record->participant_count,
                'participant_count' => $this->record->participant_count, // alias for override lookup
                'gratis_count' => $this->record->gratis_count ?? 0,
                'staff_count' => $this->record->staff_count ?? 0,
                'driver_count' => $this->record->driver_count ?? 0,
            ]
        ];
        
        $this->calculate();
    }

    public function addVariant()
    {
        $last = end($this->variants);
        // Default increment
        $newQty = $last ? $last['participant_count'] + 5 : 40;
        
        $this->variants[] = [
            'qty' => $newQty, 
            'participant_count' => $newQty,
            'gratis_count' => $last ? $last['gratis_count'] : 0,
            'staff_count' => $last ? $last['staff_count'] : 0,
            'driver_count' => $last ? $last['driver_count'] : 0,
        ];
        
        $this->calculate();
    }

    public function removeVariant($index)
    {
        if (isset($this->variants[$index])) {
            unset($this->variants[$index]);
            $this->variants = array_values($this->variants);
            $this->calculate();
        }
    }

    public function updateVariant($index, $field, $value)
    {
        if (isset($this->variants[$index])) {
            $this->variants[$index][$field] = (int)$value;
            // Sync qty alias
            if ($field === 'participant_count') {
                $this->variants[$index]['qty'] = (int)$value;
            }
        }
    }

    public function calculate()
    {
        $engine = new EventCalculationEngine();
        $this->calculationResults = [];

        foreach ($this->variants as $index => $variant) {
            // Ensure overrides array structure matches what Engine expects
            $overrides = [
                'participant_count' => max(1, (int)($variant['participant_count'] ?? 1)),
                'gratis_count' => max(0, (int)($variant['gratis_count'] ?? 0)),
                'staff_count' => max(0, (int)($variant['staff_count'] ?? 0)),
                'driver_count' => max(0, (int)($variant['driver_count'] ?? 0)),
            ];

            $this->calculationResults[$index] = $engine->calculate($this->record, $overrides);
        }
    }
    
    public function render()
    {
        return view('livewire.event-variants-calculation');
    }
}
