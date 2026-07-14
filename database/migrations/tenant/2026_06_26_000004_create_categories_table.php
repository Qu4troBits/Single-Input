<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('type', 20); // revenue, expense, transfer
            $table->string('code', 20);
            $table->text('description')->nullable();
            $table->string('color', 7)->nullable();
            $table->string('icon', 50)->nullable();
            $table->boolean('is_operating')->default(true);
            $table->boolean('is_tax_deductible')->default(false);
            $table->boolean('include_in_reports')->default(true);
            $table->boolean('is_default')->default(false);
            $table->string('parent_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices obrigatórios conforme regras arquiteturais
            $table->index(['type', 'parent_id']);
            $table->index(['code', 'type']);
            $table->index(['name', 'type']);
            $table->index(['is_operating', 'type']);
            $table->index(['is_tax_deductible', 'type']);
            $table->index(['include_in_reports', 'type']);
            $table->index(['is_default', 'type']);
            $table->index(['parent_id', 'type']);

            // Chave estrangeira para hierarquia
            $table->foreign('parent_id')
                ->references('id')
                ->on('categories')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
