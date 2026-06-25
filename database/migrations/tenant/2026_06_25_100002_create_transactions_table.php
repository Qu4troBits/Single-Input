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
            $table->id();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->string('direction', 10);
            $table->string('status', 20);
            $table->date('competence_month');
            $table->date('payment_date')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('competence_month');
            $table->index(['payment_date', 'bank_account_id']);
            $table->index(['category_id', 'competence_month']);
            $table->index(['direction', 'status', 'competence_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
