<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_projections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('type', ['revenue', 'expense', 'profit', 'cash_flow', 'balance_sheet']);
            $table->enum('period_type', ['monthly', 'quarterly', 'yearly']);
            $table->string('year_month', 7)->nullable(); // Formato: YYYY-MM
            $table->string('year', 4)->nullable(); // Formato: YYYY
            $table->integer('quarter')->nullable(); // 1, 2, 3, 4
            $table->uuid('category_id')->nullable();
            $table->string('scenario', 50)->default('base');
            $table->string('title', 255);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices obrigatórios
            $table->index(['type', 'period_type']);
            $table->index(['year_month', 'category_id']);
            $table->index(['year', 'quarter', 'scenario']);
            $table->index(['scenario', 'period_type']);

            // Chaves estrangeiras
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_projections');
    }
};