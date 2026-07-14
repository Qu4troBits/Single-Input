<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('bank_account_id');
            $table->uuid('category_id');
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->enum('direction', ['in', 'out']);
            $table->enum('status', ['pending', 'paid', 'cancelled', 'reversed'])->default('pending');
            $table->string('competence_month', 7); // Formato: YYYY-MM
            $table->date('payment_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices obrigatórios conforme arquitetura
            $table->index('competence_month');
            $table->index(['payment_date', 'bank_account_id']);
            $table->index(['category_id', 'competence_month']);
            $table->index(['direction', 'status', 'competence_month']);

            // Foreign keys
            $table->foreign('bank_account_id')
                ->references('id')
                ->on('bank_accounts')
                ->onDelete('restrict');

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};