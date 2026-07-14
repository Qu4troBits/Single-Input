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
            $table->uuid('id')->primary();
            $table->string('name');
            $table->enum('type', ['income', 'expense', 'transfer']);
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
            $table->string('color', 7)->nullable();
            $table->string('icon', 50)->nullable();
            $table->uuid('parent_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'status']);
            $table->index(['status', 'type']);
            $table->index('parent_id');

            $table->foreign('parent_id')
                ->references('id')
                ->on('categories')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};