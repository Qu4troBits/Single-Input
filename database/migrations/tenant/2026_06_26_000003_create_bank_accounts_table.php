<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('type', 20); // checking, savings, investment, credit_card, wallet, other
            $table->string('bank_code', 10);
            $table->string('bank_name');
            $table->string('agency_number', 20);
            $table->string('account_number', 30);
            $table->string('account_digit', 2)->nullable();
            $table->decimal('initial_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->string('status', 20)->default('active'); // active, inactive, closed, blocked
            $table->text('description')->nullable();
            $table->string('color', 7)->nullable();
            $table->string('icon', 50)->nullable();
            $table->boolean('include_in_dashboard')->default(true);
            $table->boolean('include_in_reports')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Índices obrigatórios conforme regras arquiteturais
            $table->index(['type', 'status']);
            $table->index(['bank_code', 'agency_number', 'account_number']);
            $table->index(['status', 'include_in_dashboard']);
            $table->index(['status', 'include_in_reports']);
            $table->index(['is_default', 'status']);
            $table->index(['created_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
