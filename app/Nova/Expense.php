<?php

namespace App\Nova;

use App\Nova\Actions\ChangeStatusToPaid;
use App\Nova\Actions\ChangeStatusToPending;
use App\Nova\Metrics\ExpensesPerType;
use App\Nova\Metrics\ExpensesVsIncomesCalculations;
use App\Nova\Metrics\PaidExpenses;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\NovaDetachedFilters\HasDetachedFilters;
use Outl1ne\NovaDetachedFilters\NovaDetachedFilters;
use Peczis\PeriodFilter\PeriodFilter;

class Expense extends Resource
{
    use HasDetachedFilters;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Expense>
     */
    public static $model = \App\Models\Expense::class;

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

            Text::make(__('Sub name'), 'sub_name')
                ->onlyOnForms(),

            Text::make(__('Repeatable key'), 'repeatable_key')
                ->hideFromIndex()
                ->hideWhenCreating(),

            Date::make(__('Date'), 'date')
                ->sortable()
                ->default(now())
                ->rules('required')
                ->hideWhenUpdating()
                ->filterable(),

            BelongsTo::make(__('Expense Type'), 'expenseType', ExpenseType::class)
                ->sortable()
                ->filterable()
                ->rules('required'),

            Select::make(__('Repeat'), 'repeat')
                ->options([
                    'each_month'    => __('Each month'),
                    'each_3_months' => __('Each 3 months'),
                    'each_year'     => __('Each year'),
                ])
                ->nullable()
                ->onlyOnForms()
                ->hideWhenUpdating(),

            Number::make(__('Repeat length'), 'repeat_length')
                ->nullable()
                ->onlyOnForms()
                ->hideWhenUpdating(),

            Badge::make('Status')
                ->map([
                    'pending' => 'info',
                    'paid'    => 'success',
                ])
                ->label(function ($value) {
                    return __($value);
                })
                ->filterable()
                ->sortable()
                ->withIcons(),

            Currency::make(__('Value'), 'value')
                ->default('1.00')
                ->step(0.0001)
                ->rules('required')
                ->sortable(),

            HasMany::make(__('Similar'), 'similar', \App\Nova\Expense::class)
                ->onlyOnDetail(),
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
            (new PaidExpenses)
                ->refreshWhenFiltersChange()
                ->refreshWhenActionsRun()
                ->width('1/2'),
            (new ExpensesVsIncomesCalculations)
                ->refreshWhenFiltersChange()
                ->refreshWhenActionsRun()
                ->width('1/2'),
            (new ExpensesPerType)
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
        return __('Expenses');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Expense');
    }

    /**
     * Get the text for the create resource button.
     *
     * @return string|null
     */
    public static function createButtonLabel()
    {
        return __('Create Expense');
    }

    /**
     * Get the text for the update resource button.
     *
     * @return string|null
     */
    public static function updateButtonLabel()
    {
        return __('Update Expense');
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        if (str_contains(base64_decode($request->filters), json_encode(PeriodFilter::class) . ':null') || !str_contains(base64_decode($request->filters), json_encode(PeriodFilter::class)))
            $query->where('period_id', current_period()->id);

        return $query
            ->when(empty($request->get('orderBy')), function (Builder $q) {
                $q->getQuery()->orders = [];

                return $q->orderBy('status')->orderBy('date');
            });
    }
}
