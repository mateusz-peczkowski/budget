<?php

namespace App\Providers;

use App\Models\Income;
use App\Observers\IncomeObserver;
use App\Models\IncomeType;
use App\Observers\IncomeTypeObserver;
use App\Models\Expense;
use App\Observers\ExpenseObserver;
use App\Models\Loan;
use App\Observers\LoanObserver;
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
        IncomeType::observe(IncomeTypeObserver::class);
        Expense::observe(ExpenseObserver::class);
        Loan::observe(LoanObserver::class);
    }
}
