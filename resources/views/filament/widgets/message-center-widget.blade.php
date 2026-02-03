<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Ostatnie aktywności
        </x-slot>

        <div class="space-y-6">
            <div>
                <div class="text-xs font-semibold uppercase text-gray-500">Nowe imprezy</div>
                <ul class="mt-2 space-y-2">
                    @forelse ($events as $event)
                        <li class="flex items-start justify-between gap-2">
                            <div class="space-y-0.5">
                                <a class="text-sm font-medium text-primary-600 hover:underline" href="{{ \App\Filament\Resources\EventResource::getUrl('edit', ['record' => $event]) }}">
                                    {{ $event->name }}
                                </a>
                                <div class="text-xs text-gray-500">
                                    Dodał: {{ $event->creator?->name ?? 'system' }}
                                </div>
                            </div>
                            <div class="text-xs text-right text-gray-500">
                                <div>{{ $event->created_at?->diffForHumans() }}</div>
                                <div class="text-[11px] text-gray-400">{{ $event->created_at?->format('d.m.Y H:i') }}</div>
                            </div>
                        </li>
                    @empty
                        <li class="text-xs text-gray-400">Brak nowych imprez</li>
                    @endforelse
                </ul>
            </div>

            <div>
                <div class="text-xs font-semibold uppercase text-gray-500">Twoje zadania</div>
                <ul class="mt-2 space-y-2">
                    @forelse ($tasks as $task)
                        <li class="flex items-start justify-between gap-2">
                            <a class="text-sm font-medium text-primary-600 hover:underline" href="{{ \App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $task]) }}">
                                {{ $task->title }}
                            </a>
                            <span class="text-xs text-gray-500">
                                {{ $task->due_date?->format('d.m') ?? 'bez terminu' }}
                            </span>
                        </li>
                    @empty
                        <li class="text-xs text-gray-400">Brak przypisanych zadań</li>
                    @endforelse
                </ul>
            </div>

            <div>
                <div class="text-xs font-semibold uppercase text-gray-500">Przeterminowane raty (zadania)</div>
                <ul class="mt-2 space-y-2">
                    @forelse ($overdueInstallmentTasks as $task)
                        <li class="flex items-start justify-between gap-2">
                            <a class="text-sm font-medium text-primary-600 hover:underline" href="{{ \App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $task]) }}">
                                {{ $task->title }}
                            </a>
                            <span class="text-xs text-gray-500">
                                {{ $task->due_date?->format('d.m') ?? 'bez terminu' }}
                            </span>
                        </li>
                    @empty
                        <li class="text-xs text-gray-400">Brak przeterminowanych zadań rat</li>
                    @endforelse
                </ul>
            </div>

            <!-- Zmiany statusów usunięte na życzenie użytkownika -->

            <div>
                <div class="text-xs font-semibold uppercase text-gray-500">Zmiany zadań</div>
                <ul class="mt-2 space-y-2">
                    @forelse ($taskChanges as $t)
                        <li class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-2">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <x-filament::icon
                                            :icon="'heroicon-o-' . ($t->icon ?? 'bolt')"
                                            class="h-4 w-4 text-gray-500 dark:text-gray-400"
                                        />
                                        <a class="text-sm font-medium text-primary-600 hover:underline truncate" href="{{ \App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $t->task_id]) }}">
                                            {{ $t->task?->title ?? 'Zadanie' }}
                                        </a>
                                        <span class="shrink-0 text-[11px] rounded-full px-2 py-0.5 bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                            {{ $t->field_label ?? ($t->field ?? 'Zmiana') }}
                                        </span>
                                    </div>

                                    <div class="mt-1 text-xs text-gray-700 dark:text-gray-200">
                                        {{ $t->summary ?? ($t->description ?? 'Zmieniono') }}
                                    </div>

                                    @if(!empty($t->old_display) || !empty($t->new_display))
                                        <div class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                            <span class="text-gray-500 dark:text-gray-400">{{ $t->old_display ?? '—' }}</span>
                                            <span class="mx-1">→</span>
                                            <span class="text-gray-900 dark:text-gray-100">{{ $t->new_display ?? '—' }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="shrink-0 text-right">
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $t->created_at?->diffForHumans() }}
                                        </div>
                                        <div class="text-[11px] text-gray-400">{{ $t->created_at?->format('d.m.Y H:i') }}</div>
                                        <div class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                                            {{ $t->user?->name ?? 'System' }}
                                        </div>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="text-xs text-gray-400">Brak zmian zadań</li>
                    @endforelse
                </ul>
            </div>

            <div>
                <div class="text-xs font-semibold uppercase text-gray-500">Przypomnienia rat</div>
                <ul class="mt-2 space-y-2">
                    @forelse ($installmentReminders as $r)
                        <li class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-2">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <x-filament::icon
                                            icon="heroicon-o-paper-airplane"
                                            class="h-4 w-4 text-gray-500 dark:text-gray-400"
                                        />
                                        <span class="shrink-0 text-[11px] rounded-full px-2 py-0.5 bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                            {{ strtoupper($r->channel ?? '-') }}
                                        </span>
                                        <span class="shrink-0 text-[11px] rounded-full px-2 py-0.5 {{ ($r->source ?? 'manual') === 'auto' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                            {{ ($r->source ?? 'manual') === 'auto' ? 'AUTO' : 'RĘCZNIE' }}
                                        </span>
                                        @if(!empty($r->contract_url))
                                            <a class="text-sm font-medium text-primary-600 hover:underline truncate" href="{{ $r->contract_url }}">
                                                {{ $r->installment?->contract?->contract_number ?? 'Umowa' }}
                                            </a>
                                        @else
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Umowa</span>
                                        @endif
                                    </div>

                                    <div class="mt-1 text-xs text-gray-700 dark:text-gray-200">
                                        {{ $r->summary ?? 'Wysłano przypomnienie' }}
                                    </div>

                                    <div class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                        Do: {{ $r->recipient ?? '-' }}
                                        @if(!empty($r->installments_url))
                                            · <a class="text-primary-600 hover:underline" href="{{ $r->installments_url }}" target="_blank" rel="noreferrer">kontrola rat</a>
                                        @endif
                                    </div>
                                </div>

                                <div class="shrink-0 text-right">
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ ($r->sent_at ?? $r->created_at)?->diffForHumans() }}
                                    </div>
                                    <div class="text-[11px] text-gray-400">
                                        {{ ($r->sent_at ?? $r->created_at)?->format('d.m.Y H:i') }}
                                    </div>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="text-xs text-gray-400">Brak wysłanych przypomnień</li>
                    @endforelse
                </ul>
            </div>

            <div>
                <div class="text-xs font-semibold uppercase text-gray-500">Maile</div>
                <ul class="mt-2 space-y-2">
                    @forelse ($emails as $email)
                        <li class="space-y-0.5">
                            <a class="text-sm font-medium text-primary-600 hover:underline" href="{{ \App\Filament\Resources\EmailMessageResource::getUrl('edit', ['record' => $email]) }}">
                                {{ $email->subject ?: '(brak tematu)' }}
                            </a>
                            <div class="text-xs text-gray-500">
                                {{ $email->from_address ?? '-' }} · {{ $email->date?->diffForHumans() }}
                            </div>
                            <div class="text-xs text-gray-400">{{ $email->date?->format('d.m.Y H:i') }}</div>
                        </li>
                    @empty
                        <li class="text-xs text-gray-400">Brak nowych maili</li>
                    @endforelse
                </ul>
            </div>

            <div>
                <div class="text-xs font-semibold uppercase text-gray-500">Wiadomości</div>
                <ul class="mt-2 space-y-2">
                    @forelse ($conversations as $conversation)
                        <li class="space-y-0.5">
                            <a class="text-sm font-medium text-primary-600 hover:underline" href="{{ \App\Filament\Resources\ConversationResource::getUrl('edit', ['record' => $conversation]) }}">
                                {{ $user ? $conversation->getDisplayName($user) : ($conversation->title ?? 'Rozmowa') }}
                            </a>
                            <div class="text-xs text-gray-500">
                                {{ $conversation->last_message_at?->diffForHumans() ?? 'brak wiadomości' }}
                            </div>
                            @if($conversation->last_message_at)
                                <div class="text-xs text-gray-400">{{ $conversation->last_message_at->format('d.m.Y H:i') }}</div>
                            @endif
                        </li>
                    @empty
                        <li class="text-xs text-gray-400">Brak nowych wiadomości</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
