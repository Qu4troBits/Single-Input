<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\PlanModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::statement('SET search_path TO public');

        PlanModel::query()->firstOrCreate(
            ['slug' => 'starter'],
            ['name' => 'Starter'],
        );

        PlanModel::query()->firstOrCreate(
            ['slug' => 'pro'],
            ['name' => 'Pro'],
        );
    }
}
