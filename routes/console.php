<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use App\Application\Tenancy\CreateTenant\CreateTenantData;
use App\Application\Tenancy\CreateTenant\CreateTenantHandler;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tenant:create {slug} {name} {plan} {--admin-name=Admin} {--admin-email=} {--admin-password=}', function () {
    $slug = (string) $this->argument('slug');
    $name = (string) $this->argument('name');
    $planSlug = (string) $this->argument('plan');

    $adminName = (string) $this->option('admin-name');
    $adminEmail = (string) ($this->option('admin-email') ?: ('admin@'.$slug.'.local'));
    $adminPassword = (string) ($this->option('admin-password') ?: Str::random(24));

    try {
        $tenant = app(CreateTenantHandler::class)->handle(new CreateTenantData(
            slug: $slug,
            name: $name,
            planSlug: $planSlug,
            adminName: $adminName,
            adminEmail: $adminEmail,
            adminPassword: $adminPassword,
        ));
    } catch (\Throwable $e) {
        $this->error($e->getMessage());

        return;
    }

    $this->info('Tenant created.');
    $this->line('slug: '.$tenant->slug->toString());
    $this->line('schema: '.$tenant->schemaName->toString());
    $this->line('admin_email: '.$adminEmail);
    $this->line('admin_password: '.$adminPassword);
})->purpose('Create a tenant (schema-per-tenant), run tenant migrations, and create the initial admin user.');
