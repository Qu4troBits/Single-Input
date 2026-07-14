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
            $table->uuid('id')->primary();
            $table->string('name');
            $table->enum('type', ['checking', 'savings', 'investment', 'credit_card', 'wallet', 'other']);
            $table->enum('status', ['active', 'inactive', 'closed'])->default('active');
            $table->string('bank_code', 10)->nullable();
            $table->string('agency', 20)->nullable();
            $table->string('account_number', 30)->nullable();
            $table->string('account_digit', 2)->nullable();
            $table->text('description')->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('initial_balance', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'type']);
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};