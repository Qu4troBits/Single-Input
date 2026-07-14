<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dre_lines', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('dre_id');
            $table->string('code', 20);
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->string('type', 20); // revenue, expense, profit
            $table->integer('level')->default(1);
            $table->boolean('is_operating')->default(true);
            $table->string('parent_id')->nullable();
            $table->string('category_id')->nullable();
            $table->string('category_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices obrigatórios
            $table->index('dre_id');
            $table->index(['dre_id', 'type']);
            $table->index(['dre_id', 'level']);
            $table->index(['dre_id', 'parent_id']);
            $table->index(['dre_id', 'category_id']);
            $table->index(['code', 'dre_id']);
            $table->index(['type', 'dre_id']);

            // Chave estrangeira
            $table->foreign('dre_id')
                ->references('id')
                ->on('dres')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dre_lines');
    }
};