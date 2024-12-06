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

class ExpensesShowTotal extends Action
{
    use InteractsWithQueue, Queueable;

    public function name()
    {
        return __('Show total');
    }

    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields $fields
     * @param \Illuminate\Support\Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        return;
    }

    /**
     * Get the fields available on the action.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        $value = 0;
        $selectedExpenses = [];

        $resources = $request->get('resources');

        if ($resources && is_array($resources) && count($resources) > 1) {
            $value = \App\Models\Expense::whereIn('id', $resources)->sum('value');

            foreach(\App\Models\Expense::whereIn('id', $resources)->get() as $expense) {
                if (!isset($selectedExpenses[$expense->name]))
                    $selectedExpenses[$expense->name] = 0;

                $selectedExpenses[$expense->name] += $expense->value;
            }
        }

        $locale = config('app.faker_locale');
        $currency = config('nova.currency');

        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);

        foreach($selectedExpenses as $key => $val)
            $selectedExpenses[$key] = $formatter->formatCurrency($val, $currency);

        return [
            KeyValue::make(__('Selected Expenses'), 'selected_expenses')
                ->readonly()
                ->keyLabel(__('Expense'))
                ->valueLabel(__('Value'))
                ->default($selectedExpenses)
                ->fullWidth()
                ->stacked(),

            Heading::make('<strong>' . __('Total') . ':</strong> ' . $formatter->formatCurrency($value, $currency))
                ->asHtml()
                ->fullWidth()
                ->stacked(),
        ];
    }
}
