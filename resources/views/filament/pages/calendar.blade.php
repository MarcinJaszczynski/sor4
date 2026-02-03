<x-filament-panels::page>
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div class="text-sm text-gray-600">
                Widoki: dzień / 7 dni / miesiąc + lista. Kliknij dzień aby dodać notatkę.
            </div>
            <div class="flex items-center gap-2">
                <a
                    href="{{ \App\Filament\Resources\EventResource::getUrl('create') }}"
                    target="_blank"
                    class="fi-btn fi-btn-color-primary"
                >
                    Nowa impreza
                </a>
                <a
                    href="{{ \App\Filament\Resources\CalendarNoteResource::getUrl('create') }}"
                    target="_blank"
                    class="fi-btn fi-btn-color-gray"
                >
                    Nowa notatka
                </a>
            </div>
        </div>

        <div
            class="bp-calendar min-h-[70vh]"
            data-bprafa-calendar
            data-feed-url="{{ url('/admin/api/calendar/feed') }}"
            data-create-note-url="{{ \App\Filament\Resources\CalendarNoteResource::getUrl('create') }}"
            data-create-event-url="{{ \App\Filament\Resources\EventResource::getUrl('create') }}"
        ></div>
    </div>
</x-filament-panels::page>
