<?php

namespace App\Nova;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class Income extends Resource
{

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Income>
     */
    public static $model = \App\Models\Income::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name', 'email',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            Stack::make(__('Name'), [
                Line::make('Name')
                    ->asHeading(),

                Line::make('Sub Name')
                    ->asSmall()
                    ->extraClasses('italic text-80'),
            ])
                ->exceptOnForms(),

            Text::make(__('Name'), 'name')
                ->sortable()
                ->rules('required')
                ->onlyOnForms(),

            Date::make(__('Date'), 'date')
                ->sortable()
                ->default(now())
                ->rules('required'),

            Select::make(__('Repeat'), 'repeat')
                ->options([
                    '2_weeks'           => __('Each 2 Weeks'),
                    'last_of_the_month' => __('Last of the month'),
                    'each_month'        => __('Each month'),
                ])
                ->sortable()
                ->onlyOnForms()
                ->hideWhenUpdating(),

            BelongsTo::make(__('Income Type'), 'incomeType', IncomeType::class)
                ->sortable()
                ->rules('required'),

            Badge::make('Status')
                ->map([
                    'pending' => 'warning',
                    'paid'    => 'success',
                ])
                ->label(function ($value) {
                    return __($value);
                })
                ->withIcons(),

            Panel::make(__('Rates'), [
                Number::make(__('Quantity'), 'quantity')
                    ->default('1.00')
                    ->min(0.01)
                    ->step(0.01)
                    ->rules('required'),

                Number::make(__('Rate'), 'rate')
                    ->rules('required')
                    ->hideFromIndex(),

                Select::make(__('Currency'), 'currency')
                    ->options(available_currencies())
                    ->default(config('nova.currency'))
                    ->rules('required')
                    ->hideFromIndex(),

                Number::make(__('Currency Rate'), 'currency_rate')
                    ->default('1.00')
                    ->min(0.0001)
                    ->step(0.0001)
                    ->rules('required')
                    ->hideFromIndex(),

                Currency::make(__('Rate Local Currency'), 'rate_local_currency')
                    ->sortable()
                    ->exceptOnForms(),
            ]),

            Panel::make(__('Salary'), [
                Currency::make(__('Net'), 'net')
                    ->sortable()
                    ->exceptOnForms(),

                Currency::make(__('Gross'), 'gross')
                    ->sortable()
                    ->exceptOnForms(),
            ]),

            Panel::make(__('Taxes Settings'), [
                Number::make(__('Tax Percent'), 'tax_percent')
                    ->default('0.00')
                    ->min(0)
                    ->max(100)
                    ->step(0.01)
                    ->rules('required')
                    ->hideFromIndex()
                    ->displayUsing(function ($value) {
                        return $value . '%';
                    }),

                Currency::make(__('Tax'), 'tax')
                    ->sortable()
                    ->exceptOnForms(),

                Number::make(__('VAT Percent'), 'vat_percent')
                    ->default('0.00')
                    ->min(0)
                    ->max(100)
                    ->step(0.01)
                    ->rules('required')
                    ->hideFromIndex()
                    ->displayUsing(function ($value) {
                        return $value . '%';
                    }),

                Currency::make(__('Vat'), 'vat')
                    ->sortable()
                    ->exceptOnForms(),
            ]),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     *
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     *
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     *
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     *
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }

    /**
     * Determine if the resource can be replicated.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     *
     * @return bool
     */
    public function authorizedToReplicate(Request $request)
    {
        return false;
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Incomes');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Income');
    }

    /**
     * Get the text for the create resource button.
     *
     * @return string|null
     */
    public static function createButtonLabel()
    {
        return __('Create Income');
    }

    /**
     * Get the text for the update resource button.
     *
     * @return string|null
     */
    public static function updateButtonLabel()
    {
        return __('Update Income');
    }

    public static function indexQuery(NovaRequest $request, $query) {
        return $query
            ->where('period_id', current_period()->id)
            ->when(empty($request->get('orderBy')), function(Builder $q) {
                $q->getQuery()->orders = [];

                return $q->orderBy('date');
            });
    }
}
