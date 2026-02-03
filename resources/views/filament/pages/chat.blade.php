<x-filament-panels::page>
    <style>
        /* Prefer targeted overrides to avoid breaking utility-based highlights
           Keep backgrounds for neutral areas light, but don't override primary/bg utilities */
        .chat-override .bg-white,
        .chat-override .bg-gray-50 {
            background-color: #ffffff !important;
        }

        /* Replace dark-mode light text in chat with dark text to ensure readability on light backgrounds */
        .chat-override .dark\:text-gray-100,
        .chat-override .dark\:text-white,
        .chat-override .text-white {
            color: #0f172a !important;
        }

        /* Slightly dim less important meta text */
        .chat-override .dark\:text-gray-400,
        .chat-override .dark\:text-gray-500,
        .chat-override .text-gray-400,
        .chat-override .text-gray-500 {
            color: #6b7280 !important; /* gray-500 */
        }

        /* Make checkbox tick (accent) clearly visible (black) */
        .chat-override input[type="checkbox"] {
            accent-color: #0f172a !important; /* near-black */
        }
        .chat-override input[type="checkbox"]:checked {
            border-color: #0f172a !important;
            background-color: #0f172a !important;
        }
    </style>
    <div class="h-[calc(100vh-200px)] min-h-[600px] bg-white dark:bg-gray-900 rounded-lg overflow-hidden shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
        @livewire('chat-interface', ['conversationId' => $conversationId])
    </div>
</x-filament-panels::page>
