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
        Schema::table('expenses', function (Blueprint $table) {
            $table->date('pay_date')->after('date')->nullable();
        });

        foreach (DB::table('expenses')->where('status', 'paid')->get() as $expense) {
            DB::table('expenses')
                ->where('id', $expense->id)
                ->update([
                    'pay_date' => $expense->date
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('pay_date');
        });
    }
};
