<x-filament::page>
    <div
        x-data="{ rightOpen: JSON.parse(localStorage.getItem('dashboardRightOpen') ?? 'true') }"
        x-init="$watch('rightOpen', v => localStorage.setItem('dashboardRightOpen', JSON.stringify(v)))"
    >
        <div class="mb-4 flex items-center justify-end">
            <button
                type="button"
                @click="rightOpen = !rightOpen"
                class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm"
            >
                <x-heroicon-m-adjustments-horizontal class="w-4 h-4" />
                <span x-text="rightOpen ? 'Zwiń aktywności' : 'Pokaż aktywności'"></span>
            </button>
        </div>

        <div class="mb-6">
            <x-filament-widgets::widgets :widgets="$this->getHeaderWidgets()" :columns="$this->getColumns()" />
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
            <div class="space-y-6 lg:col-span-1">
                <x-filament-widgets::widgets :widgets="$this->getLeftWidgets()" :columns="1" />
            </div>

            <div class="space-y-6 lg:col-span-2">
                <x-filament-widgets::widgets :widgets="$this->getCenterWidgets()" :columns="1" />
            </div>

            <div class="space-y-6 lg:col-span-1" x-show="rightOpen" x-transition x-cloak>
                <x-filament-widgets::widgets :widgets="$this->getRightWidgets()" :columns="1" />
            </div>
        </div>
    </div>
</x-filament::page>
