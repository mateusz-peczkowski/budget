<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Http\Requests\NovaRequest;

class IncomesShowTotal extends Action
{
    use InteractsWithQueue, Queueable;

    public function name()
    {
        return __('Show total');
    }

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        return;
    }

    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        $gross = 0;
        $net = 0;
        $tax = 0;
        $vat = 0;
        $selectedIncomes = [];

        $resources = $request->get('resources');

        if ($resources && is_array($resources) && count($resources) > 1) {
            $gross = \App\Models\Income::whereIn('id', $resources)->sum('gross');
            $net = \App\Models\Income::whereIn('id', $resources)->sum('net');
            $tax = \App\Models\Income::whereIn('id', $resources)->sum('tax');
            $vat = \App\Models\Income::whereIn('id', $resources)->sum('vat');

            foreach(\App\Models\Income::whereIn('id', $resources)->get() as $income) {
                if (!isset($selectedIncomes[$income->name]))
                    $selectedIncomes[$income->name] = 0;

                $selectedIncomes[$income->name] += $income->gross;
            }
        }

        $locale = config('app.faker_locale');
        $currency = config('nova.currency');

        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);

        foreach($selectedIncomes as $key => $val)
            $selectedIncomes[$key] = $formatter->formatCurrency($val, $currency);

        return [
            KeyValue::make(__('Selected Incomes'), 'selected_incomes')
                ->readonly()
                ->keyLabel(__('Income'))
                ->valueLabel(__('Gross'))
                ->default($selectedIncomes)
                ->fullWidth()
                ->stacked(),

            Heading::make('<strong>' . __('Total Gross') . ':</strong> ' . $formatter->formatCurrency($gross, $currency))
                ->asHtml()
                ->fullWidth()
                ->stacked(),

            Heading::make('<strong>' . __('Total Net') . ':</strong> ' . $formatter->formatCurrency($net, $currency))
                ->asHtml()
                ->fullWidth()
                ->stacked(),

            Heading::make('------------')
                ->fullWidth()
                ->stacked(),

            Heading::make('<strong>' . __('Total Tax') . ':</strong> ' . $formatter->formatCurrency($tax, $currency))
                ->asHtml()
                ->fullWidth()
                ->stacked(),

            Heading::make('<strong>' . __('Total VAT') . ':</strong> ' . $formatter->formatCurrency($vat, $currency))
                ->asHtml()
                ->fullWidth()
                ->stacked(),
        ];
    }
}
