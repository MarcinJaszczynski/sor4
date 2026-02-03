<x-filament-panels::page>
    <div class="filament-header-actions" style="display:flex; gap:0.5rem; align-items:center; margin-bottom:0.75rem;">
        <a href="{{ \App\Filament\Resources\EventResource::getUrl('edit', ['record' => $event->id]) }}" class="filament-button filament-button-size-sm">
            ← Powrót do edycji imprezy
        </a>
    </div>

    <div>
        @livewire('event-instance-program-tree-editor', ['event' => $event])
    </div>
</x-filament-panels::page>
