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
        Schema::table('tax_settlements', function (Blueprint $table) {
            $table->float('to_pay')->nullable()->after('file');
            $table->float('excess')->nullable()->after('to_pay');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_settlements', function (Blueprint $table) {
            $table->dropColumn('to_pay');
            $table->dropColumn('excess');
        });
    }
};
