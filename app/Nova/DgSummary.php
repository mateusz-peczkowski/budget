<?php

namespace App\Nova;

use App\Nova\Metrics\DgYearCompletion;
use App\Nova\Metrics\DgSummaryIncomes;
use App\Nova\Metrics\DgSummaryExpenses;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Mimosu\NovaSplitDateInput\SplitDate;
use Outl1ne\NovaDetachedFilters\HasDetachedFilters;
use Outl1ne\NovaDetachedFilters\NovaDetachedFilters;
use Peczis\DgYearFilter\DgYearFilter;

class DgSummary extends Resource
{
    use HasDetachedFilters;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\DgSummary>
     */
    public static $model = \App\Models\DgSummary::class;

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

        if ($latest = $this->model()->latest('date')->first())
            $defaultDate = $latest->date->addMonth()->startOfMonth();

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

            Panel::make(__('Incomes'), [
                Currency::make(__('Net'), 'net')
                    ->displayUsing(function ($value) {
                        return '<span class="text-yellow-500">' . (new Currency(''))->formatMoney($value ?? 0) . '</span>';
                    })
                    ->asHtml()
                    ->rules('required'),

                Currency::make(__('VAT'), 'gross_vat')
                    ->onlyOnForms()
                    ->rules('required'),

                Currency::make(__('Gross'), 'gross')
                    ->displayUsing(function ($value) {
                        return '<span class="text-yellow-500">' . (new Currency(''))->formatMoney($value ?? 0) . '</span>';
                    })
                    ->asHtml()
                    ->exceptOnForms(),
            ]),

            Panel::make(__('Expenses'), [
                Currency::make(__('ZUS'), 'zus')
                    ->displayUsing(function ($value) {
                        return '<span class="text-red-500">' . (new Currency(''))->formatMoney($value ?? 0) . '</span>';
                    })
                    ->asHtml()
                    ->rules('required'),

                Currency::make(__('Tax'), 'tax')
                    ->displayUsing(function ($value) {
                        return '<span class="text-red-500">' . (new Currency(''))->formatMoney($value ?? 0) . '</span>';
                    })
                    ->asHtml()
                    ->rules('required'),

                Currency::make(__('VAT'), 'vat')
                    ->displayUsing(function ($value) {
                        return '<span class="text-red-500">' . (new Currency(''))->formatMoney($value ?? 0) . '</span>';
                    })
                    ->asHtml()
                    ->rules('required'),
            ]),

            Text::make(__('Revenue'), function () {
                $revenue =  $this->gross - $this->zus - $this->tax - $this->vat;

                return '<span class="text-green-500">' . (new Currency(''))->formatMoney($revenue ?? 0) . '</span>';
            })
                ->onlyOnIndex()
                ->asHtml(),

            Panel::make(__('Files'), [
                File::make(__('DG Complete Document'), 'complete_document')
                    ->disk('public')
                    ->onlyOnForms(),

                Text::make(__('DG Complete Document'), function () {
                    return $this->complete_document ? '<a href="' . URL::to('/storage/' . $this->complete_document) . '" target="_blank" class="text-center"><svg xmlns="http://www.w3.org/2000/svg" style="display: inline-block;" class="h-10 w-10" viewBox="0 0 20 20" fill="currentColor">
  <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd" />
</svg></a>' : '-';
                })
                    ->exceptOnForms()
                    ->asHtml()
                    ->withMeta([
                        'textAlign' => 'center',
                    ]),

                File::make(__('ZUS Document'), 'zus_document')
                    ->disk('public')
                    ->onlyOnForms(),

                Text::make(__('ZUS Document'), function () {
                    return $this->zus_document ? '<a href="' . URL::to('/storage/' . $this->zus_document) . '" target="_blank" class="text-center"><svg xmlns="http://www.w3.org/2000/svg" style="display: inline-block;" class="h-10 w-10" viewBox="0 0 20 20" fill="currentColor">
  <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd" />
</svg></a>' : '-';
                })
                    ->exceptOnForms()
                    ->asHtml()
                    ->withMeta([
                        'textAlign' => 'center',
                    ]),

                File::make(__('Tax Document'), 'tax_document')
                    ->disk('public')
                    ->onlyOnForms(),

                Text::make(__('Tax Document'), function () {
                    return $this->tax_document ? '<a href="' . URL::to('/storage/' . $this->tax_document) . '" target="_blank" class="text-center"><svg xmlns="http://www.w3.org/2000/svg" style="display: inline-block;" class="h-10 w-10" viewBox="0 0 20 20" fill="currentColor">
  <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd" />
</svg></a>' : '-';
                })
                    ->exceptOnForms()
                    ->asHtml()
                    ->withMeta([
                        'textAlign' => 'center',
                    ]),

                File::make(__('VAT Document'), 'vat_document')
                    ->disk('public')
                    ->onlyOnForms(),

                Text::make(__('VAT Document'), function () {
                    return $this->vat_document ? '<a href="' . URL::to('/storage/' . $this->vat_document) . '" target="_blank" class="text-center"><svg xmlns="http://www.w3.org/2000/svg" style="display: inline-block;" class="h-10 w-10" viewBox="0 0 20 20" fill="currentColor">
  <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd" />
</svg></a>' : '-';
                })
                    ->exceptOnForms()
                    ->asHtml()
                    ->withMeta([
                        'textAlign' => 'center',
                    ]),

                File::make(__('DG Documents Archive'), 'documents_archive')
                    ->disk('public')
                    ->onlyOnForms(),

                Text::make(__('DG Documents Archive'), function () {
                    return $this->documents_archive ? '<a href="' . URL::to('/storage/' . $this->documents_archive) . '" target="_blank" class="text-center"><svg xmlns="http://www.w3.org/2000/svg" style="display: inline-block;" class="h-10 w-10" viewBox="0 0 20 20" fill="currentColor">
  <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd" />
</svg></a>' : '-';
                })
                    ->exceptOnForms()
                    ->asHtml()
                    ->withMeta([
                        'textAlign' => 'center',
                    ]),
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
            (new DgYearCompletion)
                ->refreshWhenFiltersChange()
                ->refreshWhenActionsRun()
                ->width('1/2'),
            (new DgSummaryIncomes)
                ->refreshWhenFiltersChange()
                ->refreshWhenActionsRun()
                ->width('1/2'),
            (new DgSummaryExpenses)
                ->refreshWhenFiltersChange()
                ->refreshWhenActionsRun()
                ->width('1/2'),
        ];
    }

    protected function myFilters()
    {
        return [
            new DgYearFilter,
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
        return __('DG Summaries');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('DG Summary');
    }

    /**
     * Get the text for the create resource button.
     *
     * @return string|null
     */
    public static function createButtonLabel()
    {
        return __('Create DG Summary');
    }

    /**
     * Get the text for the update resource button.
     *
     * @return string|null
     */
    public static function updateButtonLabel()
    {
        return __('Update DG Summary');
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        if (str_contains(base64_decode($request->filters), json_encode(DgYearFilter::class) . ':null') || !str_contains(base64_decode($request->filters), json_encode(DgYearFilter::class)))
            $query->whereBetween('date', [now()->startOfYear(), now()->endOfYear()]);

        return $query
            ->when(empty($request->get('orderBy')), function (Builder $q) {
                $q->getQuery()->orders = [];

                return $q->orderBy('date', 'DESC');
            });
    }

    public function title()
    {
        return ucfirst($this->date->isoFormat('MMMM Y'));
    }
}
