<?php

declare(strict_types=1);

namespace A2ZWeb\AuditableRelations;

use Illuminate\Support\ServiceProvider;

class AuditableRelationsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Currently no bootstrapping needed
        // The package works purely through the trait
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // No services to register yet
    }
}
