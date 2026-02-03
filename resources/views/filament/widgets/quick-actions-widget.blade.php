<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Szybkie Akcje
        </x-slot>

        <div class="grid grid-cols-2 gap-4">
            <a href="{{ \App\Filament\Resources\EventResource::getUrl('create') }}" class="flex flex-col items-center justify-center p-4 bg-primary-50 rounded-lg hover:bg-primary-100 transition text-primary-600">
                <x-heroicon-o-plus-circle class="w-8 h-8 mb-2" />
                <span class="font-bold text-sm">Nowa Impreza</span>
            </a>

            <a href="{{ \App\Filament\Resources\ContractResource::getUrl('create') }}" class="flex flex-col items-center justify-center p-4 bg-primary-50 rounded-lg hover:bg-primary-100 transition text-primary-600">
                <x-heroicon-o-document-plus class="w-8 h-8 mb-2" />
                <span class="font-bold text-sm">Nowa Umowa</span>
            </a>

            <a href="{{ \App\Filament\Resources\TaskResource::getUrl('index') }}" class="flex flex-col items-center justify-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition text-gray-600">
                <x-heroicon-o-clipboard-document-list class="w-8 h-8 mb-2" />
                <span class="font-bold text-sm">Zadania</span>
            </a>

            <a href="{{ \App\Filament\Resources\EventPaymentResource::getUrl('index') }}" class="flex flex-col items-center justify-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition text-gray-600">
                <x-heroicon-o-currency-dollar class="w-8 h-8 mb-2" />
                <span class="font-bold text-sm">Płatności</span>
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
