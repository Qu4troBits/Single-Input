<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dres', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->date('period_start');
            $table->date('period_end');
            $table->string('period_type', 20); // monthly, quarterly, yearly, custom, consolidated
            $table->string('title');
            $table->string('category_id')->nullable();
            $table->string('scenario', 50)->default('base');
            $table->decimal('total_revenue', 15, 2);
            $table->decimal('total_expenses', 15, 2);
            $table->decimal('net_profit', 15, 2);
            $table->decimal('gross_profit', 15, 2);
            $table->decimal('operating_profit', 15, 2);
            $table->decimal('ebitda', 15, 2);
            $table->decimal('ebit', 15, 2);
            $table->timestamp('generated_at');
            $table->timestamps();
            $table->softDeletes();

            // Índices obrigatórios conforme regras arquiteturais
            $table->index(['period_start', 'period_end']);
            $table->index(['category_id', 'period_start']);
            $table->index(['scenario', 'period_start']);
            $table->index(['period_type', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dres');
    }
};