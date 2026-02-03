<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Js;
use Filament\Support\Assets\Css;
use Illuminate\Support\Facades\Vite;
use Filament\Tables\Table;
use App\Models\Place;
use App\Models\Contract;
use App\Models\ContractInstallment;
use App\Models\Event;
use App\Models\EventPayment;
use App\Models\EventCost;
use App\Models\TaskHistory;
use App\Models\TaskComment;
use App\Observers\PlaceObserver;
use App\Observers\ContractInstallmentObserver;
use App\Observers\ContractObserver;
use App\Observers\EventObserver;
use App\Observers\EventPaymentObserver;
use App\Observers\EventCostObserver;
use App\Observers\TaskHistoryObserver;
use App\Observers\TaskCommentObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Rejestracja serwisu SMS - domyślnie LogSmsGateway (demo)
        // W przyszłości można tu dać warunek np. if(env('SMS_DRIVER') == 'smsapi') ...
        $this->app->bind(
            \App\Services\Sms\SmsGatewayInterface::class, 
            \App\Services\Sms\Drivers\LogSmsGateway::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentAsset::register([
            Css::make('app-styles', Vite::asset('resources/css/app.css')),
            Js::make('app-scripts', Vite::asset('resources/js/app.js'))->module(), // Dodaj ->module()
        ]);
        
        // Rejestracja komponenty Blade dla powiadomień
        $this->app['blade.compiler']->component('app.filament.components.topbar-notifications', 'app-filament-components-topbar-notifications');

        // Auto-generate place distance pairs on place create/update
        Place::observe(PlaceObserver::class);

        ContractInstallment::observe(ContractInstallmentObserver::class);
        Contract::observe(ContractObserver::class);
        Event::observe(EventObserver::class);
        EventPayment::observe(EventPaymentObserver::class);
        EventCost::observe(EventCostObserver::class);
        TaskHistory::observe(TaskHistoryObserver::class);
        TaskComment::observe(TaskCommentObserver::class);
    }
}
