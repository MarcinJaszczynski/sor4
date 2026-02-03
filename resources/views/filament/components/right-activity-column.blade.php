<div class="right-activity-column-wrapper hidden lg:block" style="position: absolute; width: 0; height: 0; padding: 0; margin: 0; overflow: hidden; pointer-events: none;">
    <style>
        @media (min-width: 1024px) {
            .right-activity-column-wrapper .right-activity-column {
                pointer-events: auto;
                position: fixed;
                right: 0;
                top: 4rem; /* leave space for topbar */
                height: calc(100vh - 4rem);
                width: 24rem;
                overflow: auto;
                z-index: 30;
            }
        }
    </style>

    <div id="right-activity-column" class="right-activity-column" role="complementary" aria-label="Ostatnie aktywności">
        <div class="space-y-4 p-3">
            <div class="bg-white rounded-lg shadow p-3">
                <x-filament-widgets::widgets :widgets="[\App\Filament\Widgets\MessageCenterWidget::class]" :columns="1" />
            </div>
            <div class="bg-white rounded-lg shadow p-3">
                <x-filament-widgets::widgets :widgets="[\App\Filament\Widgets\CalendarOrganizerWidget::class]" :columns="1" />
            </div>
        </div>
    </div>

    <script>
        (function () {
            const col = document.getElementById('right-activity-column');
            if (!col) return;

            const getMain = () =>
                document.querySelector('.fi-main')
                || document.querySelector('.fi-main-ctn')
                || document.querySelector('main');

            const applyLayout = () => {
                const topbar = document.querySelector('.fi-topbar');
                const main = getMain();
                const mainPaddingTop = main ? parseFloat(getComputedStyle(main).paddingTop || '0') : 0;
                const top = (topbar ? topbar.offsetHeight : 64) + mainPaddingTop;
                const width = col.offsetWidth || 384; // fallback
                col.style.top = top + 'px';
                col.style.height = `calc(100vh - ${top}px)`;
                // reserve space only on desktop
                if (window.innerWidth >= 1024) {
                    if (main) {
                        main.style.marginRight = width + 'px';
                    }
                } else {
                    if (main) {
                        main.style.marginRight = null;
                    }
                }
            };

            // visibility from localStorage (default true)
            const updateVisibility = () => {
                const visible = localStorage.getItem('rightActivityVisible');
                const isVisible = visible === null ? true : visible === '1';
                col.style.display = isVisible && window.innerWidth >= 1024 ? '' : 'none';
                const main = getMain();
                if (main) {
                    main.style.marginRight = (isVisible && window.innerWidth >= 1024) ? (col.offsetWidth || 384) + 'px' : null;
                }
            };

            window.addEventListener('resize', () => {
                applyLayout();
                updateVisibility();
            });

            window.addEventListener('toggleRightActivity', () => {
                const cur = localStorage.getItem('rightActivityVisible');
                const next = cur === '1' ? '0' : '1';
                localStorage.setItem('rightActivityVisible', next);
                updateVisibility();
            });

            // initial
            applyLayout();
            updateVisibility();
            // reapply after a short delay to handle livewire/topbar rendering
            setTimeout(() => applyLayout(), 250);
        })();
    </script>
</div>
