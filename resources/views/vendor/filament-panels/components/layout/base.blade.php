@props([
    'livewire' => null,
])

<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ __('filament-panels::layout.direction') ?? 'ltr' }}"
    @class([
        'fi min-h-screen',
        'dark' => filament()->hasDarkModeForced(),
    ])
>
    <head>
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::HEAD_START, scopes: $livewire->getRenderHookScopes()) }}

        <meta charset="utf-8" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <script>
            (function () {
                const key = 'theme';
                const stored = localStorage.getItem(key);
                const normalized = stored === 'dark' || stored === 'light' ? stored : 'light';
                if (stored !== normalized) {
                    localStorage.setItem(key, normalized);
                }
                const html = document.documentElement;
                html.classList.toggle('dark', normalized === 'dark');
                html.setAttribute('data-theme', normalized);
            })();
        </script>

        @if ($favicon = filament()->getFavicon())
            <link rel="icon" href="{{ $favicon }}" />
        @endif

        @php
            $title = trim(strip_tags(($livewire ?? null)?->getTitle() ?? ''));
            $brandName = trim(strip_tags(filament()->getBrandName()));
        @endphp

        <title>
            {{ filled($title) ? "{$title} - " : null }} {{ $brandName }}
        </title>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::STYLES_BEFORE, scopes: $livewire->getRenderHookScopes()) }}

        <style>
            [x-cloak=''],
            [x-cloak='x-cloak'],
            [x-cloak='1'] {
                display: none !important;
            }

            @media (max-width: 1023px) {
                [x-cloak='-lg'] {
                    display: none !important;
                }
            }

            @media (min-width: 1024px) {
                [x-cloak='lg'] {
                    display: none !important;
                }
            }
        </style>

        <style>
            /* Kompaktowy sidebar */
            .fi-sidebar-item .fi-sidebar-item-button {
                padding-left: .25rem;
                padding-right: .25rem;
                padding-top: .25rem;
                padding-bottom: .25rem;
            }
            .fi-sidebar-item-icon { height: 1.25rem; width: 1.25rem; }
            .fi-sidebar-item-label { font-size: .75rem; }

            /* Mniejsze marginesy grup i elementów */
            .fi-sidebar-sub-group-items { gap: .125rem; }

            /* Kompaktowe formularze: mniejsze odstępy w wrapperach */
            .fi-input-wrp { padding: .375rem; }

            /* Dymki błędów: upewnij się, że są czytelne */
            [data-validation-error] { cursor: help; }

                /* Compact mode and collapsed sidebar styles */
                body.filament-compact-mode .fi-sidebar-item-icon {
                    height: 0.875rem !important;
                    width: 0.875rem !important;
                }

                body.filament-compact-mode .fi-sidebar-item-button {
                    padding-left: .125rem !important;
                    padding-right: .125rem !important;
                }

                body.filament-compact-mode .fi-sidebar-item-label {
                    font-size: .6875rem !important;
                }

                /* Collapsed: hide labels and make sidebar narrow */
                body.filament-sidebar-collapsed .fi-sidebar-item-label,
                body.filament-sidebar-collapsed .fi-sidebar-sub-group-items {
                    display: none !important;
                }

                body.filament-sidebar-collapsed .fi-sidebar-item-button {
                    justify-content: center !important;
                    padding-left: .25rem !important;
                    padding-right: .25rem !important;
                }

                body.filament-sidebar-collapsed .fi-sidebar {
                    width: var(--collapsed-sidebar-width) !important;
                }
        </style>

        @filamentStyles

        {{ filament()->getTheme()->getHtml() }}
        {{ filament()->getFontHtml() }}

        <style>
            :root {
                --font-family: '{!! filament()->getFontFamily() !!}';
                --sidebar-width: {{ filament()->getSidebarWidth() }};
                --collapsed-sidebar-width: {{ filament()->getCollapsedSidebarWidth() }};
                --default-theme-mode: {{ filament()->getDefaultThemeMode()->value }};
            }
        </style>

        @stack('styles')

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::STYLES_AFTER, scopes: $livewire->getRenderHookScopes()) }}

        @if (! filament()->hasDarkMode())
            <script>
                localStorage.setItem('theme', 'light')
            </script>
        @elseif (filament()->hasDarkModeForced())
            <script>
                localStorage.setItem('theme', 'dark')
            </script>
        @else
            <script>
                const loadDarkMode = () => {
                    window.theme = localStorage.getItem('theme') ?? @js(filament()->getDefaultThemeMode()->value)

                    if (
                        window.theme === 'dark' ||
                        (window.theme === 'system' &&
                            window.matchMedia('(prefers-color-scheme: dark)')
                                .matches)
                    ) {
                        document.documentElement.classList.add('dark')
                    }
                }

                loadDarkMode()

                document.addEventListener('livewire:navigated', loadDarkMode)
            </script>
        @endif

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::HEAD_END, scopes: $livewire->getRenderHookScopes()) }}
    </head>

    <body
        {{ $attributes
                ->merge(($livewire ?? null)?->getExtraBodyAttributes() ?? [], escape: false)
                ->class([
                    'fi-body',
                    'fi-panel-' . filament()->getId(),
                    'min-h-screen bg-gray-50 font-normal text-gray-950 antialiased dark:bg-gray-950 dark:text-white',
                ]) }}
    >
            <!-- Compact mode / Collapse controls (fixed) -->
            <div id="filament-compact-controls" class="fixed top-3 left-3 z-50 flex gap-2">
                <button id="filament-toggle-compact" type="button" title="Przełącz kompaktowy widok" class="filament-compact-btn inline-flex items-center justify-center rounded-md bg-white/80 px-2 py-1 text-xs shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <button id="filament-toggle-collapse" type="button" title="Zwiń/rozwiń sidebar" class="filament-collapse-btn inline-flex items-center justify-center rounded-md bg-white/80 px-2 py-1 text-xs shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::BODY_START, scopes: $livewire->getRenderHookScopes()) }}

        {{ $slot }}

        @livewire(Filament\Livewire\Notifications::class)

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SCRIPTS_BEFORE, scopes: $livewire->getRenderHookScopes()) }}

        @filamentScripts(withCore: true)

        @if (filament()->hasBroadcasting() && config('filament.broadcasting.echo'))
            <script data-navigate-once>
                window.Echo = new window.EchoFactory(@js(config('filament.broadcasting.echo')))

                window.dispatchEvent(new CustomEvent('EchoLoaded'))
            </script>
        @endif

        @if (filament()->hasDarkMode() && (! filament()->hasDarkModeForced()))
            <script>
                loadDarkMode()
            </script>
        @endif

        @stack('scripts')

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SCRIPTS_AFTER, scopes: $livewire->getRenderHookScopes()) }}

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::BODY_END, scopes: $livewire->getRenderHookScopes()) }}
    </body>
</html>

    <script>
        (function () {
            const COMPACT_KEY = 'filament-compact-mode';
            const COLLAPSE_KEY = 'filament-sidebar-collapsed';

            const applyState = () => {
                const compact = localStorage.getItem(COMPACT_KEY) === '1';
                const collapsed = localStorage.getItem(COLLAPSE_KEY) === '1';

                if (compact) document.body.classList.add('filament-compact-mode'); else document.body.classList.remove('filament-compact-mode');
                if (collapsed) document.body.classList.add('filament-sidebar-collapsed'); else document.body.classList.remove('filament-sidebar-collapsed');
            }

            document.addEventListener('DOMContentLoaded', function () {
                const btnCompact = document.getElementById('filament-toggle-compact');
                const btnCollapse = document.getElementById('filament-toggle-collapse');

                applyState();

                if (btnCompact) btnCompact.addEventListener('click', function () {
                    const v = localStorage.getItem(COMPACT_KEY) === '1' ? '0' : '1';
                    localStorage.setItem(COMPACT_KEY, v);
                    applyState();
                });

                if (btnCollapse) btnCollapse.addEventListener('click', function () {
                    const v = localStorage.getItem(COLLAPSE_KEY) === '1' ? '0' : '1';
                    localStorage.setItem(COLLAPSE_KEY, v);
                    applyState();
                });
            });
        })();
    </script>
