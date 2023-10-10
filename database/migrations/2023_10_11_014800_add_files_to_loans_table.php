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
            $table->text('file_1')->after('remaining_payments_years')->nullable();
            $table->text('file_2')->after('file_1')->nullable();
            $table->text('file_3')->after('file_2')->nullable();
            $table->text('file_4')->after('file_3')->nullable();
            $table->text('file_5')->after('file_4')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('file_1');
            $table->dropColumn('file_2');
            $table->dropColumn('file_3');
            $table->dropColumn('file_4');
            $table->dropColumn('file_5');
        });
    }
};
