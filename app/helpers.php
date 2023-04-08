<?php

if (!function_exists('available_currencies')) {
    function available_currencies()
    {
        return [
            'PLN' => 'PLN',
            'USD' => 'USD',
            'EUR' => 'Euro',
        ];
    }
}

if (!function_exists('current_period')) {
    function current_period()
    {
        return \App\Models\Period::whereHas('incomes', function ($q) {
            $q->where('status', 'pending');
        })
            ->first(); //TO-DO Peczis: Update to check as well expenses when done
    }
}
