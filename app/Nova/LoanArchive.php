<?php

namespace App\Nova;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class LoanArchive extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Loan>
     */
    public static $model = \App\Models\Loan::class;

    /**
     * The number of resources to show per page via relationships.
     *
     * @var int
     */
    public static $perPageViaRelationship = 20;


    public static $perPageOptions = [50, 25, 10];

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'title',
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

            Text::make(__('Name'), 'title')
                ->sortable()
                ->rules('required'),

            Badge::make('Status')
                ->map([
                    'current' => 'info',
                    'late'    => 'warning',
                    'paid'    => 'success',
                    'archive' => 'success',
                ])
                ->label(function ($value) {
                    return __($value);
                })
                ->filterable()
                ->sortable()
                ->withIcons()
                ->exceptOnForms(),

            Text::make(__('Who'), function () {
                return '<div style="display: flex; align-items: center;"><img src="' . ($this->user ? URL::to('/storage/' . $this->user->avatar) : 'https://ui-avatars.com/api/?name=' . $this->who) . '" class="rounded-full w-8 h-8 mr-3" /></div>';
            })
                ->exceptOnForms()
                ->sortable()
                ->asHtml(),

            Text::make(__('Who'), 'who')
                ->rules('required')
                ->onlyOnForms(),

            Panel::make(__('Loan info'), [
                Date::make(__('Last Payment'), 'last_payment')
                    ->exceptOnForms()
                    ->sortable(),

                Currency::make(__('Overall Value'), 'overall_value')
                    ->default('1.00')
                    ->step(0.0001)
                    ->sortable()
                    ->exceptOnForms(),

                Currency::make(__('Paid Value'), 'paid_value')
                    ->default('1.00')
                    ->step(0.0001)
                    ->sortable()
                    ->onlyOnDetail(),

                Currency::make(__('Remaining Value'), 'remaining_value')
                    ->default('1.00')
                    ->step(0.0001)
                    ->sortable()
                    ->onlyOnDetail(),

                Text::make(__('Paid Percent'), 'paid_percent')
                    ->displayUsing(function($value) {
                        return $value . '%';
                    })
                    ->sortable()
                    ->onlyOnDetail(),

                Currency::make(__('Next Payment Value'), 'next_payment_value')
                    ->default('1.00')
                    ->step(0.0001)
                    ->sortable()
                    ->onlyOnDetail(),

                Number::make(__('Remaining Payments Count'), 'remaining_payments_count')
                    ->sortable()
                    ->onlyOnDetail(),

                Number::make(__('Remaining Payments Years'), 'remaining_payments_years')
                    ->sortable()
                    ->onlyOnDetail(),

                Date::make(__('Date Ending'), 'date_ending')
                    ->onlyOnDetail()
                    ->sortable(),
            ]),

            HasMany::make(__('Payments'), 'payments', \App\Nova\Expense::class)
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
        return __('Loans archive');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Loan archive');
    }

    /**
     * Get the text for the create resource button.
     *
     * @return string|null
     */
    public static function createButtonLabel()
    {
        return __('Create Loan');
    }

    /**
     * Get the text for the update resource button.
     *
     * @return string|null
     */
    public static function updateButtonLabel()
    {
        return __('Update Loan');
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query
            ->where('status', 'archive')
            ->when(empty($request->get('orderBy')), function (Builder $q) {
                $q->getQuery()->orders = [];

                return $q->orderBy('last_payment');
            });
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }
}
