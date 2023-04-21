<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->date('pay_date')->after('date')->nullable();
        });

        foreach (DB::table('incomes')->where('status', 'paid')->get() as $income) {
            DB::table('incomes')
                ->where('id', $income->id)
                ->update([
                    'pay_date' => $income->date
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropColumn('pay_date');
        });
    }
};
