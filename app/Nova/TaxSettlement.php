<?php

namespace App\Nova;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class TaxSettlement extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\TaxSettlement>
     */
    public static $model = \App\Models\TaxSettlement::class;

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
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
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

            BelongsTo::make(__('Tax Payer'), 'user', User::class)
                ->sortable()
                ->filterable()
                ->rules('required')
                ->onlyOnForms(),

            Text::make(__('Tax Payer'), function () {
                return '<div style="display: flex; align-items: center;"><img src="' . ($this->user ? URL::to('/storage/' . $this->user->avatar) : 'https://ui-avatars.com/api/?name=' . $this->who) . '" class="rounded-full w-8 h-8 mr-3" />' . ($this->user ? '<p>' . $this->user->name . '</p>' : '') . '</div>';
            })
                ->sortable()
                ->asHtml()
                ->exceptOnForms(),

            BelongsTo::make(__('Tax Settlement Type'), 'taxSettlementType', TaxSettlementType::class)
                ->sortable()
                ->rules('required')
                ->displayUsing(function ($taxSettlementType) {
                    return $taxSettlementType->name . ($taxSettlementType->issuer ? ' (' . $taxSettlementType->issuer . ')' : '');
                })
                ->onlyOnForms(),

            Stack::make(__('Tax Settlement Type'), [
                Line::make('Name', 'name')
                    ->asHeading()
                    ->displayUsing(function () {
                        return $this->taxSettlementType ? $this->taxSettlementType->name : '-';
                    }),

                Line::make('Issuer Tax', 'issuer')
                    ->asSmall()
                    ->extraClasses('italic text-80')
                    ->displayUsing(function () {
                        return $this->taxSettlementType ? $this->taxSettlementType->issuer : '-';
                    }),
            ])
                ->exceptOnForms(),

            Date::make(__('Submit date'), 'submit_date')
                ->sortable()
                ->default(now())
                ->rules('required'),

            File::make(__('File'), 'file')
                ->disk('public')
                ->hideFromIndex(),

            Text::make(__('File'), function () {
                return $this->file ? '<a href="' . URL::to('/storage/' . $this->file) . '" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 20 20" fill="currentColor">
  <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd" />
</svg></a>' : '-';
            })
                ->onlyOnIndex()
                ->sortable()
                ->asHtml(),
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
        return [
            new \App\Nova\Filters\TaxSettlementType,
            new \App\Nova\Filters\TaxSettlementYear,
        ];
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
        return __('Tax Settlements');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Tax Settlement');
    }

    /**
     * Get the text for the create resource button.
     *
     * @return string|null
     */
    public static function createButtonLabel()
    {
        return __('Create Tax Settlement');
    }

    /**
     * Get the text for the update resource button.
     *
     * @return string|null
     */
    public static function updateButtonLabel()
    {
        return __('Update Tax Settlement');
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query
            ->when(empty($request->get('orderBy')), function (Builder $q) {
                $q->getQuery()->orders = [];

                return $q->orderBy('submit_date', 'DESC');
            });
    }
}
