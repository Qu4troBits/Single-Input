<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reconciliation_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('bank_account_id');
            $table->date('date');
            $table->string('description', 255);
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'reconciled', 'discrepancy', 'adjusted']);
            $table->uuid('transaction_id')->nullable();
            $table->string('bank_statement_id', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices obrigatórios conforme regras arquiteturais
            $table->index(['bank_account_id', 'date']);
            $table->index(['status', 'date']);
            $table->index(['transaction_id']);
            $table->index(['bank_statement_id']);

            // Chaves estrangeiras
            $table->foreign('bank_account_id')
                ->references('id')
                ->on('bank_accounts')
                ->onDelete('restrict');

            $table->foreign('transaction_id')
                ->references('id')
                ->on('transactions')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reconciliation_items');
    }
};