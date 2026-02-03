<?php

namespace App\Providers;

use App\Models\Bus;
use App\Models\Markup;
use App\Models\Task;
use App\Policies\BusPolicy;
use App\Policies\MarkupPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Bus::class => BusPolicy::class,
        Markup::class => MarkupPolicy::class,
        Task::class => TaskPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
