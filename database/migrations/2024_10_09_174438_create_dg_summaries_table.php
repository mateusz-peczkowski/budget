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
        Schema::create('dg_summaries', function (Blueprint $table) {
            $table->id();

            $table->date('date')->index();

            $table->double('gross', 10, 2)->nullable();
            $table->double('net', 10, 2)->nullable();

            $table->double('zus', 10, 2)->nullable();
            $table->double('tax', 10, 2)->nullable();
            $table->double('vat', 10, 2)->nullable();

            $table->text('complete_document')->nullable();
            $table->text('zus_document')->nullable();
            $table->text('tax_document')->nullable();
            $table->text('vat_document')->nullable();
            $table->text('documents_archive')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dg_summaries');
    }
};
