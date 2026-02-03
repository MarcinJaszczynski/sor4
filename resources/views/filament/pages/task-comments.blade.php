<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
            <div class="p-4 border-b border-gray-200 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Nowe komentarze</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Lista komentarzy do przeczytania</p>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse($unread as $notification)
                    @php
                        $data = $notification->data ?? [];
                        $taskTitle = $data['task_title'] ?? 'Zadanie';
                        $authorName = $data['author_name'] ?? 'System';
                        $recipientName = $data['recipient_name'] ?? 'Użytkownik';
                        $recipientNames = $data['recipient_names'] ?? [];
                        $eventName = $data['event_name'] ?? null;
                        $eventCode = $data['event_code'] ?? null;
                        $taskAuthorName = $data['task_author_name'] ?? null;
                        $taskAssigneeName = $data['task_assignee_name'] ?? null;
                        $taskableType = $data['taskable_type'] ?? null;
                        $excerpt = $data['comment_excerpt'] ?? '';
                    @endphp
                    <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-sm text-gray-900 dark:text-gray-100 font-semibold truncate">
                                    {{ $taskTitle }}
                                </div>
                                @if($eventName)
                                    <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                        Impreza: {{ $eventName }}@if($eventCode) ({{ $eventCode }})@endif
                                    </div>
                                @elseif($taskableType)
                                    <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                        Dotyczy: {{ class_basename($taskableType) }}
                                    </div>
                                @endif
                                @if($taskAuthorName || $taskAssigneeName)
                                    <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                        Autor zadania: {{ $taskAuthorName ?? '—' }} · Zleceniobiorca: {{ $taskAssigneeName ?? '—' }}
                                    </div>
                                @endif
                                <div class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                    Od: {{ $authorName }} →
                                    @if(!empty($recipientNames))
                                        {{ implode(', ', $recipientNames) }}
                                    @else
                                        {{ $recipientName }}
                                    @endif
                                </div>
                                <div class="mt-1 text-sm text-gray-700 dark:text-gray-200">
                                    {{ $excerpt }}
                                </div>
                                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $notification->created_at?->diffForHumans() }} · {{ $notification->created_at?->format('d.m.Y H:i') }}
                                </div>
                                <div class="mt-3">
                                    <details class="text-sm">
                                        <summary class="cursor-pointer text-primary-600">Odpowiedz</summary>
                                        <form method="POST" action="{{ route('admin.task-comments.reply', ['task' => $data['task_id'] ?? 0]) }}" class="mt-2 space-y-2">
                                            @csrf
                                            <input type="hidden" name="notification_id" value="{{ $notification->id }}" />
                                            <textarea name="content" rows="3" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 p-2 text-sm" placeholder="Napisz odpowiedź..."></textarea>
                                            <button type="submit" class="inline-flex items-center rounded-md bg-primary-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-primary-700">
                                                Wyślij odpowiedź
                                            </button>
                                        </form>
                                    </details>
                                </div>
                            </div>
                            <div class="shrink-0">
                                <a href="{{ route('admin.notifications.task-comments.open', ['notification' => $notification->id]) }}"
                                   class="inline-flex items-center rounded-md bg-primary-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-primary-700">
                                    Przejdź do zadania
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-sm text-gray-500 dark:text-gray-400">
                        Brak nowych komentarzy.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
            <div class="p-4 border-b border-gray-200 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Ostatnio przeczytane</h2>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse($recentRead as $notification)
                    @php
                        $data = $notification->data ?? [];
                        $taskTitle = $data['task_title'] ?? 'Zadanie';
                        $authorName = $data['author_name'] ?? 'System';
                        $recipientName = $data['recipient_name'] ?? 'Użytkownik';
                        $recipientNames = $data['recipient_names'] ?? [];
                        $eventName = $data['event_name'] ?? null;
                        $eventCode = $data['event_code'] ?? null;
                        $taskAuthorName = $data['task_author_name'] ?? null;
                        $taskAssigneeName = $data['task_assignee_name'] ?? null;
                        $taskableType = $data['taskable_type'] ?? null;
                        $excerpt = $data['comment_excerpt'] ?? '';
                    @endphp
                    <div class="p-4">
                        <div class="text-sm text-gray-900 dark:text-gray-100 font-semibold truncate">
                            {{ $taskTitle }}
                        </div>
                        @if($eventName)
                            <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                Impreza: {{ $eventName }}@if($eventCode) ({{ $eventCode }})@endif
                            </div>
                        @elseif($taskableType)
                            <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                Dotyczy: {{ class_basename($taskableType) }}
                            </div>
                        @endif
                        @if($taskAuthorName || $taskAssigneeName)
                            <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                Autor zadania: {{ $taskAuthorName ?? '—' }} · Zleceniobiorca: {{ $taskAssigneeName ?? '—' }}
                            </div>
                        @endif
                        <div class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                            Od: {{ $authorName }} →
                            @if(!empty($recipientNames))
                                {{ implode(', ', $recipientNames) }}
                            @else
                                {{ $recipientName }}
                            @endif
                        </div>
                        <div class="mt-1 text-sm text-gray-700 dark:text-gray-200">
                            {{ $excerpt }}
                        </div>
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            {{ $notification->created_at?->diffForHumans() }} · {{ $notification->created_at?->format('d.m.Y H:i') }}
                        </div>
                        <div class="mt-3">
                            <details class="text-sm">
                                <summary class="cursor-pointer text-primary-600">Odpowiedz</summary>
                                <form method="POST" action="{{ route('admin.task-comments.reply', ['task' => $data['task_id'] ?? 0]) }}" class="mt-2 space-y-2">
                                    @csrf
                                    <input type="hidden" name="notification_id" value="{{ $notification->id }}" />
                                    <textarea name="content" rows="3" class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 p-2 text-sm" placeholder="Napisz odpowiedź..."></textarea>
                                    <button type="submit" class="inline-flex items-center rounded-md bg-primary-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-primary-700">
                                        Wyślij odpowiedź
                                    </button>
                                </form>
                            </details>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-sm text-gray-500 dark:text-gray-400">
                        Brak historii komentarzy.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-panels::page>
