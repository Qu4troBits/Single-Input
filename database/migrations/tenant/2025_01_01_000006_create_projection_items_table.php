<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projection_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('projection_id');
            $table->date('date');
            $table->string('description', 255);
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['revenue', 'expense', 'profit', 'cash_flow', 'balance_sheet']);
            $table->uuid('category_id')->nullable();
            $table->string('category_name', 100)->nullable();
            $table->text('notes')->nullable();
            $table->string('source', 50)->nullable(); // 'historical', 'manual', 'formula'
            $table->timestamps();
            $table->softDeletes();

            // Índices obrigatórios
            $table->index(['projection_id', 'date']);
            $table->index(['type', 'category_id']);
            $table->index(['date', 'source']);
            $table->index(['category_id', 'type']);

            // Chaves estrangeiras
            $table->foreign('projection_id')
                ->references('id')
                ->on('financial_projections')
                ->onDelete('cascade');

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projection_items');
    }
};