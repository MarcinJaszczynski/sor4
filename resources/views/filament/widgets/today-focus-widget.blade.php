<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Na dziś</x-slot>

        <div class="grid grid-cols-1 gap-3 text-sm">
            <div class="flex items-center justify-between">
                <div>Zaległe raty</div>
                <a class="text-primary-600 hover:underline" href="{{ \App\Filament\Pages\Finance\InstallmentControl::getUrl(['scope' => 'overdue']) }}">{{ $overdueCount }} • {{ number_format($overdueAmount, 2, ',', ' ') }} PLN</a>
            </div>
            <div class="flex items-center justify-between">
                <div>Raty na dziś</div>
                <a class="text-primary-600 hover:underline" href="{{ \App\Filament\Pages\Finance\InstallmentControl::getUrl(['scope' => 'soon', 'days' => 0]) }}">{{ $dueTodayCount }} • {{ number_format($dueTodayAmount, 2, ',', ' ') }} PLN</a>
            </div>
            <div class="flex items-center justify-between">
                <div>Zaległe zadania (raty)</div>
                <a class="text-primary-600 hover:underline" href="{{ \App\Filament\Resources\TaskResource::getUrl('index', ['installments' => 1]) }}">{{ $overdueTasks }}</a>
            </div>
            <div class="flex items-center justify-between">
                <div>Raport zaległości</div>
                <a class="text-primary-600 hover:underline" href="{{ \App\Filament\Pages\Finance\OverdueInstallmentsReport::getUrl() }}">Otwórz</a>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
