<h3 class="mb-2 px-2 font-semibold text-sm {{ $status['text_class'] ?? 'text-gray-600' }} flex items-center justify-between uppercase tracking-wide">
    <span>{{ $status['title'] }}</span>
    <span class="text-xs font-normal opacity-70 bg-white/50 dark:bg-black/20 px-2 py-0.5 rounded-full">
        {{ count($status['records']) }}
    </span>
</h3>
