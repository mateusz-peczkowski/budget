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
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('sub_name')->nullable();
            $table->string('repeatable_key')->nullable();

            $table->date('date');

            $table->enum('status', ['pending', 'paid']);

            $table->foreignId('income_type_id')
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('period_id')
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->double('quantity', 8, 2)->default(1);

            $table->double('rate', 8, 2)->nullable();

            $table->string('currency')->default(config('nova.currency'));
            $table->double('currency_rate', 10, 4)->nullable()->default(1);

            $table->double('rate_local_currency', 8, 2)->nullable();

            $table->double('net', 10, 2)->nullable();
            $table->double('gross', 10, 2)->nullable();

            $table->double('tax_percent', 5, 2)->nullable();
            $table->double('tax', 8, 2)->nullable();

            $table->double('vat_percent', 5, 2)->nullable();
            $table->double('vat', 8, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
