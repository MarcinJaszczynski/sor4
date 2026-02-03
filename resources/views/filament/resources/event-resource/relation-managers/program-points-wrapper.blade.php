@php
    $ownerRecord = $this->getOwnerRecord();
@endphp

<div class="space-y-4">
    @include('filament.resources.event-resource.relation-managers.program-points-summary', [
        'calculations' => $this->calculations,
        'transportCost' => $this->transportCost,
    ])
    
    <!-- Tutaj będzie wyświetlona tablica -->
    @livewire(
        \Filament\Resources\RelationManagers\RelationManager::class,
        [
            'ownerRecord' => $ownerRecord,
            'relationship' => 'programPoints',
        ]
    )
</div>
