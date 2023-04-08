<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpenseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $expenseTypes = [
            'Kredyt',
            'DG - Podatki',
            'DG - Leasing',
            'DG - Opłaty',
            'DG - Inne',
            'Multimedia',
            'Dom',
            'Kuba',
            'Natan',
            'Oszczędności',
            'Na życie',
            'Inne',
        ];

        foreach ($expenseTypes as $name) {
            DB::table('expense_types')->insert([
                'name'       => $name,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
