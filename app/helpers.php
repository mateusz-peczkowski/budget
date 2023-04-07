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
        return \App\Models\Period::where('is_closed', false)->first();
    }
}
