<?php

namespace App\Providers;

use App\Nova\Expense;
use App\Nova\ExpenseType;
use App\Nova\ExpenseFile;
use App\Nova\Income;
use App\Nova\IncomeType;
use App\Nova\LoanActive;
use App\Nova\LoanArchive;
use App\Nova\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Peczis\ClosestExpensesAndIncomes\ClosestExpensesAndIncomes;
use Peczis\YearlyCalculations\YearlyCalculations;
use Peczis\YearlyCalculationsDg\YearlyCalculationsDg;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Nova::withoutThemeSwitcher();
        Nova::withoutNotificationCenter();

        Nova::mainMenu(function (Request $request) {
            return [
                MenuSection::make(__('Dashboards'), [
                    (new ClosestExpensesAndIncomes)->menu($request),
                    (new YearlyCalculations)->menu($request),
                    (new YearlyCalculationsDg)->menu($request),
                ])
                    ->icon('chart-bar'),

                MenuSection::make(__('Expenses Tab'), [
                    MenuItem::resource(Expense::class),
                    MenuItem::resource(ExpenseType::class),
                    MenuItem::resource(ExpenseFile::class),
                ])
                    ->icon('credit-card'),

                MenuSection::make(__('Incomes Tab'), [
                    MenuItem::resource(Income::class),
                    MenuItem::resource(IncomeType::class),
                ])
                    ->icon('currency-dollar'),

                MenuSection::make(__('Loans Tab'), [
                    MenuItem::resource(LoanActive::class),
                    MenuItem::resource(LoanArchive::class),
                ])
                    ->icon('office-building'),

                MenuSection::make('Users')
                    ->resource(User::class)
                    ->icon('users')
            ];
        });

        Nova::footer(function ($request) {
            return Blade::render('
                <p class="text-center">Designed and developed by <a class="link-default" href="https://peczis.pl" target="_blank">peczis.pl</a> © 2023</p>
            ');
        });
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()
                ->withAuthenticationRoutes()
                ->withPasswordResetRoutes()
                ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            return in_array($user->email, \App\Models\User::all()->pluck('email')->toArray());
        });
    }

    /**
     * Get the dashboards that should be listed in the Nova sidebar.
     *
     * @return array
     */
    protected function dashboards()
    {
        return [];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        return [
            new ClosestExpensesAndIncomes,
            new YearlyCalculations,
            new YearlyCalculationsDg,
        ];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Nova::initialPath('/closest-expenses-and-incomes');

        Nova::report(function ($exception) {
            if (app()->bound('sentry') && (env('APP_ENV') === 'staging' || env('APP_ENV') === 'production')) {
                app('sentry')->captureException($exception);
            }
        });
    }
}
