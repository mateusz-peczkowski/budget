<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IncomeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $incomeTypes = [
            'DG - Mateusz' => 2219.09,
            'Pozostałe'    => 0,
            'Blockchain'   => 0,
        ];

        foreach ($incomeTypes as $name => $zus) {
            DB::table('income_types')->insert([
                'name'       => $name,
                'zus'        => $zus,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
