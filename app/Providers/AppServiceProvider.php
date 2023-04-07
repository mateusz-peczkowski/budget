<?php

namespace App\Providers;

use App\Models\Income;
use App\Observers\IncomeObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Income::observe(IncomeObserver::class);
    }
}
