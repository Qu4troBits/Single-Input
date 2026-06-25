<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Tenancy\TenantContextInterface;
use App\Domain\Tenancy\TenantRepositoryInterface;
use App\Domain\Tenancy\ValueObjects\TenantSlug;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final readonly class TenantMiddleware
{
    public function __construct(
        private TenantRepositoryInterface $tenants,
        private TenantContextInterface $tenantContext,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $slugString = $this->resolveTenantSlugFromHost($request->getHost());

        if ($slugString === null) {
            abort(404);
        }

        try {
            $slug = TenantSlug::fromString($slugString);
        } catch (\Throwable) {
            abort(404);
        }

        $tenant = $this->tenants->findBySlug($slug);

        if ($tenant === null) {
            abort(404);
        }

        $this->tenantContext->set($tenant);

        DB::statement(sprintf('SET search_path TO "%s", public', $this->escapeIdentifier($tenant->schemaName->toString())));

        return $next($request);
    }

    private function resolveTenantSlugFromHost(string $host): ?string
    {
        if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return null;
        }

        $parts = explode('.', $host);

        if (count($parts) < 2) {
            return null;
        }

        return $parts[0] !== '' ? $parts[0] : null;
    }

    private function escapeIdentifier(string $identifier): string
    {
        return str_replace('"', '""', $identifier);
    }
}
