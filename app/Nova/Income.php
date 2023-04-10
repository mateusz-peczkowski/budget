<?php

namespace App\Nova;

use App\Nova\Actions\ChangeStatusToPaid;
use App\Nova\Actions\ChangeStatusToPending;
use App\Nova\Metrics\IncomeExpensesCalculations;
use App\Nova\Metrics\IncomeNetGross;
use App\Nova\Metrics\PaidIncomes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
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
use Outl1ne\NovaDetachedFilters\HasDetachedFilters;
use Outl1ne\NovaDetachedFilters\NovaDetachedFilters;
use Peczis\PeriodFilter\PeriodFilter;

class Income extends Resource
{
    use HasDetachedFilters;

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
        'id', 'name',
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
            ID::make()
                ->hideFromIndex(),

            Stack::make(__('Name'), [
                Line::make('Name')
                    ->asHeading()
                    ->filterable(function ($request, $query, $value, $attribute) {
                        $query->where($attribute, 'LIKE', "%{$value}%");
                    }),

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
                ->rules('required')
                ->hideWhenUpdating(),

            Avatar::make(__('Owner'), 'incomeType.user.avatar')
                ->rounded()
                ->disableDownload()
                ->exceptOnForms(),

            BelongsTo::make(__('Income Type'), 'incomeType', IncomeType::class)
                ->sortable()
                ->filterable()
                ->rules('required'),

            Select::make(__('Repeat'), 'repeat')
                ->options([
                    '2_weeks'           => __('Each 2 Weeks'),
                    'last_of_the_month' => __('Last of the month'),
                    'each_month'        => __('Each month'),
                ])
                ->nullable()
                ->onlyOnForms()
                ->hideWhenUpdating(),

            Boolean::make(__('Update Future Incomes'), 'update_future_incomes')
                ->onlyOnForms()
                ->hideWhenCreating()
                ->showOnUpdating(function () {
                    return $this->repeatable_key !== NULL;
                }),

            Badge::make('Status')
                ->map([
                    'pending' => 'info',
                    'paid'    => 'success',
                ])
                ->label(function ($value) {
                    return __($value);
                })
                ->filterable()
                ->withIcons(),

            Panel::make(__('Rates'), [
                Number::make(__('Quantity'), 'quantity')
                    ->default('1.00')
                    ->min(0.01)
                    ->step(0.01)
                    ->rules('required'),

                Number::make(__('Rate'), 'rate')
                    ->min(0.01)
                    ->step(0.01)
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
        return [
            (new NovaDetachedFilters($this->myFilters()))
                ->width('1/2'),
            (new PaidIncomes)
                ->refreshWhenFiltersChange()
                ->refreshWhenActionsRun()
                ->width('1/2'),
            (new IncomeNetGross)
                ->refreshWhenFiltersChange()
                ->refreshWhenActionsRun()
                ->width('1/2'),
            (new IncomeExpensesCalculations)
                ->refreshWhenFiltersChange()
                ->refreshWhenActionsRun()
                ->width('1/2'),
        ];
    }

    protected function myFilters()
    {
        return [
            new PeriodFilter,
        ];
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
        return [
            (new ChangeStatusToPaid)
                ->showInline(),
            (new ChangeStatusToPending)
                ->showInline(),
        ];
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

    public static function indexQuery(NovaRequest $request, $query)
    {
        if (str_contains(base64_decode($request->filters), json_encode(PeriodFilter::class) . ':null') || !str_contains(base64_decode($request->filters), json_encode(PeriodFilter::class)))
            $query->where('period_id', current_period()->id);

        return $query
            ->when(empty($request->get('orderBy')), function (Builder $q) {
                $q->getQuery()->orders = [];

                return $q->orderBy('date');
            });
    }
}
