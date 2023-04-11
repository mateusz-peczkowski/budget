<?php

namespace App\Providers;

use App\Nova\Dashboards\IncomingExpensesAndIncomes;
use App\Nova\Expense;
use App\Nova\ExpenseType;
use App\Nova\Income;
use App\Nova\IncomeType;
use App\Nova\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;

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
                MenuSection::make(__('Statistics'), [
                    MenuItem::dashboard(IncomingExpensesAndIncomes::class),
                ])
                    ->icon('credit-card'),

                MenuSection::make(__('Expenses Tab'), [
                    MenuItem::resource(Expense::class),
                    MenuItem::resource(ExpenseType::class),
                ])
                    ->icon('credit-card'),

                MenuSection::make(__('Incomes Tab'), [
                    MenuItem::resource(Income::class),
                    MenuItem::resource(IncomeType::class),
                ])
                    ->icon('currency-dollar'),

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
        return [
            new IncomingExpensesAndIncomes,
        ];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        return [];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Nova::initialPath('/dashboards/incoming-expenses-and-incomes');

        Nova::report(function ($exception) {
            if (app()->bound('sentry') && (env('APP_ENV') === 'staging' || env('APP_ENV') === 'production')) {
                app('sentry')->captureException($exception);
            }
        });
    }
}
