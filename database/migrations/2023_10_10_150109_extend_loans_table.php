<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->date('last_payment')->after('notes')->nullable();
            $table->double('overall_value', 10, 2)->after('last_payment')->nullable();
            $table->double('paid_value', 10, 2)->after('overall_value')->nullable();
            $table->double('remaining_value', 10, 2)->after('paid_value')->nullable();
            $table->double('paid_percent', 5, 2)->after('remaining_value')->nullable();
            $table->double('next_payment_value', 10, 2)->after('paid_percent')->nullable();
            $table->integer('remaining_payments_count')->after('next_payment_value')->nullable();
            $table->integer('remaining_payments_years')->after('remaining_payments_count')->nullable();
            $table->date('date_ending')->after('remaining_payments_years')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('last_payment');
            $table->dropColumn('overall_value');
            $table->dropColumn('paid_value');
            $table->dropColumn('remaining_value');
            $table->dropColumn('paid_percent');
            $table->dropColumn('next_payment_value');
            $table->dropColumn('remaining_payments_count');
            $table->dropColumn('remaining_payments_years');
            $table->dropColumn('date_ending');
        });
    }
};
