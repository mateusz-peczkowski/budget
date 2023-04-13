<?php

namespace Database\Seeders;

use App\Jobs\RecalculateTaxesAndVatsForPeriods;
use Illuminate\Database\Seeder;

class RecalculateTaxesAndVatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        dispatch(new RecalculateTaxesAndVatsForPeriods(\App\Models\Period::all()->pluck('id')));
    }
}
