<?php

namespace App\Nova;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Mimosu\NovaSplitDateInput\SplitDate;

class DgAgreement extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\DgAgreement>
     */
    public static $model = \App\Models\DgAgreement::class;

    /**
     * The number of resources to show per page via relationships.
     *
     * @var int
     */
    public static $perPageViaRelationship = 20;


    public static $perPageOptions = [50, 25, 10];

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'date',
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
        $defaultDate = now()->startOfMonth();

        $options = [];

        foreach (\App\Models\Income::pluck('name', 'repeatable_key')->toArray() as $key => $name) {
            $options[] = [
                'label' => $name,
                'value' => $key,
            ];
        }

        return [
            ID::make()
                ->hideFromIndex(),

            SplitDate::make(__('Date'), 'date')
                ->delimiters(__('Year'), __('Month'), __('Day'))
                ->limitYears(2019, now()->year)
                ->rules('required', 'date')
                ->default($defaultDate)
                ->onlyOnForms()
                ->hideWhenUpdating()
                ->creationRules('unique:dg_summaries,date'),

            Text::make(__('Date'), function () {
                return ucfirst(Carbon::parse($this->date)->isoFormat('MMMM Y'));
            })
                ->exceptOnForms(),

            Text::make(__('Title'), 'title')
                ->rules('required'),

            Select::make(__('Company'), 'company')
                ->options($options)
                ->rules('required')
                ->displayUsingLabels()
                ->filterable(),

            File::make(__('Document'), 'document')
                ->disk('public')
                ->onlyOnForms(),

            Text::make(__('Document'), function () {
                return $this->document ? '<a href="' . URL::to('/storage/' . $this->document) . '" target="_blank" class="text-center"><svg xmlns="http://www.w3.org/2000/svg" style="display: inline-block;" class="h-10 w-10" viewBox="0 0 20 20" fill="currentColor">
<path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd" />
</svg></a>' : '-';
            })
                ->exceptOnForms()
                ->asHtml()
                ->withMeta([
                    'textAlign' => 'center',
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
        return __('DG Agreements');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('DG Agreement');
    }

    /**
     * Get the text for the create resource button.
     *
     * @return string|null
     */
    public static function createButtonLabel()
    {
        return __('Create DG Agreement');
    }

    /**
     * Get the text for the update resource button.
     *
     * @return string|null
     */
    public static function updateButtonLabel()
    {
        return __('Update DG Agreement');
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query
            ->when(empty($request->get('orderBy')), function (Builder $q) {
                $q->getQuery()->orders = [];

                return $q->orderBy('date', 'DESC');
            });
    }

    public function title()
    {
        return ucfirst($this->date->isoFormat('MMMM Y')) . ' - ' . $this->title;
    }
}
