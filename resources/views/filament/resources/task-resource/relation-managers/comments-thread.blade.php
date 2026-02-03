<div class="space-y-4">
    {{-- Główny komentarz --}}
    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
        <div class="flex items-start justify-between mb-2">
            <div class="text-sm font-medium text-gray-900 dark:text-white">
                {{ $comment->author->name ?? 'System' }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ $comment->created_at->format('d.m.Y H:i') }}
            </div>
        </div>
        
        @if($comment->recipients && $comment->recipients->count() > 0)
            <div class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                <span class="font-medium">Do:</span> {{ $comment->recipients->pluck('name')->join(', ') }}
            </div>
        @endif
        
        <div class="prose dark:prose-invert max-w-none text-sm">
            {!! $comment->content !!}
        </div>
    </div>

    {{-- Odpowiedzi --}}
    @if($comment->replies && $comment->replies->count() > 0)
        <div class="ml-8 space-y-3">
            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Odpowiedzi ({{ $comment->replies->count() }}):
            </div>
            
            @foreach($comment->replies as $reply)
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">
                    <div class="flex items-start justify-between mb-2">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $reply->author->name ?? 'System' }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $reply->created_at->format('d.m.Y H:i') }}
                        </div>
                    </div>
                    
                    @if($reply->recipients && $reply->recipients->count() > 0)
                        <div class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                            <span class="font-medium">Do:</span> {{ $reply->recipients->pluck('name')->join(', ') }}
                        </div>
                    @endif
                    
                    <div class="prose dark:prose-invert max-w-none text-sm">
                        {!! $reply->content !!}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
