<?php

namespace App\Providers\Filament;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use App\Filament\Pages\ImportExportPanel;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Filament\Navigation\NavigationGroup;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandLogo(asset('uploads/logo.png'))
            ->brandLogoHeight('2.25rem')
            ->brandName('bprafa')
            ->maxContentWidth('full')
            ->darkMode(false)
            ->colors([
                'primary' => Color::Teal,
                'gray' => Color::Slate,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
                'danger' => Color::Rose,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
                ImportExportPanel::class,
            ])
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\Widgets\QuickActionsWidget::class, // Sort 1
                \App\Filament\Widgets\DashboardStatsOverview::class, // Sort ? (BaseWidget doesn't interpret sort property here, standard widgets are listed in order)
                \App\Filament\Widgets\TodayFocusWidget::class,
                \App\Filament\Widgets\LatestEventsWidget::class, // Sort 2
                \App\Filament\Widgets\MessageCenterWidget::class, // Sort 3
                \App\Filament\Widgets\CalendarOrganizerWidget::class, // Sort 4
            ])
            ->plugin(FilamentShieldPlugin::make())
            ->navigationGroups([
                NavigationGroup::make('Admin')
                    ->label('Admin')
                    ->collapsed(),
                NavigationGroup::make('Imprezy')
                    ->label('Imprezy')
                    ->collapsed(false),
                NavigationGroup::make('Ustawienia')
                    ->label('Ustawienia')
                    ->collapsed(),
                NavigationGroup::make('Ustawienia kalkulacji')
                    ->label('Ustawienia kalkulacji')
                    ->collapsed(),
                NavigationGroup::make('Ustawienia ogólne')
                    ->label('Ustawienia ogólne')
                    ->collapsed(),
                NavigationGroup::make('Ustawienia noclegów')
                    ->label('Ustawienia noclegów')
                    ->collapsed(),
                NavigationGroup::make('Ustawienia transportu')
                    ->label('Ustawienia transportu')
                    ->collapsed(),
                NavigationGroup::make('Kontakty')
                    ->label('Kontakty')
                    ->collapsed(),
                NavigationGroup::make('Szablony imprez')
                    ->label('Szablony imprez')
                    ->collapsed(),
                NavigationGroup::make('Zadania')
                    ->label('Zadania')
                    ->collapsed(),
                NavigationGroup::make('Komunikacja')
                    ->label('Komunikacja')
                    ->collapsed(),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                PanelsRenderHook::TOPBAR_END,
                function (): string {
                    $user = Auth::user();
                    if (!$user) {
                        return '';
                    }

                    $counts = \App\Services\NotificationService::getUnreadCountsForUser($user->id);
                    
                    return view('filament.components.topbar-notifications', [
                        'newTasksCount' => $counts['tasks'],
                        'unreadMessagesCount' => $counts['messages'],
                        'unreadEmailsCount' => $counts['emails'] ?? 0,
                        'unreadCommentsCount' => $counts['task_comments'] ?? 0,
                    ])->render();
                }
            )
            ->renderHook(
                PanelsRenderHook::BODY_START,
                function (): string {
                    // Render a right-side activity column visible on desktop across all Filament pages
                    return view('filament.components.right-activity-column')->render();
                }
            );
    }
}
