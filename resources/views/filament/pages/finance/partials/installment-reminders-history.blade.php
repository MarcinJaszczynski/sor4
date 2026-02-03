<div class="space-y-3">
    @if(($reminders ?? collect())->isEmpty())
        <div class="text-sm text-gray-500">Brak wysłanych przypomnień dla tej raty.</div>
    @else
        <div class="space-y-2">
            @foreach($reminders as $r)
                <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] rounded-full px-2 py-0.5 bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                    {{ strtoupper($r->channel ?? '-') }}
                                </span>
                                <span class="text-[11px] rounded-full px-2 py-0.5 {{ ($r->source ?? 'manual') === 'auto' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ ($r->source ?? 'manual') === 'auto' ? 'AUTO' : 'RĘCZNIE' }}
                                </span>
                                <span class="text-xs text-gray-500">Do: {{ $r->recipient ?? '-' }}</span>
                            </div>

                            <div class="mt-2 text-xs text-gray-700 dark:text-gray-200 whitespace-pre-wrap">{{ $r->message }}</div>
                        </div>

                        <div class="shrink-0 text-right">
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $r->sent_at?->diffForHumans() }}
                            </div>
                            <div class="text-[11px] text-gray-400">
                                {{ $r->sent_at?->format('d.m.Y H:i') }}
                            </div>
                            <div class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                                {{ $r->user?->name ?? 'system' }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
