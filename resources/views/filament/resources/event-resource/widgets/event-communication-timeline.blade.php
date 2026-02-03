<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Oś komunikacji</x-slot>

        <div class="space-y-4">
            <div>
                <div class="text-xs font-semibold uppercase text-gray-500">E-maile</div>
                <ul class="mt-2 space-y-2">
                    @forelse($emails as $email)
                        <li class="flex items-start justify-between gap-2">
                            <a class="text-sm font-medium text-primary-600 hover:underline" href="{{ \App\Filament\Resources\EmailMessageResource::getUrl('edit', ['record' => $email]) }}">
                                {{ $email->subject ?: '(brak tematu)' }}
                            </a>
                            <span class="text-xs text-gray-500">{{ $email->date?->format('d.m') }}</span>
                        </li>
                    @empty
                        <li class="text-xs text-gray-400">Brak maili</li>
                    @endforelse
                </ul>
            </div>

            <div>
                <div class="text-xs font-semibold uppercase text-gray-500">Przypomnienia rat</div>
                <ul class="mt-2 space-y-2">
                    @forelse($reminders as $r)
                        <li class="flex items-start justify-between gap-2">
                            <div class="text-sm text-gray-700 dark:text-gray-200">
                                {{ strtoupper($r->channel ?? '-') }} • {{ $r->recipient ?? '-' }}
                            </div>
                            <span class="text-xs text-gray-500">{{ $r->sent_at?->format('d.m') }}</span>
                        </li>
                    @empty
                        <li class="text-xs text-gray-400">Brak przypomnień</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
