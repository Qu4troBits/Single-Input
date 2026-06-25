<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Infrastructure\Persistence\Eloquent\Models\AuditLogModel;
use Symfony\Component\HttpFoundation\Response;

final class AuditLogMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $userId = Auth::id();

        if ($userId === null) {
            return $response;
        }

        DB::statement('SET search_path TO public');

        AuditLogModel::query()->create([
            'user_id' => $userId,
            'ip_address' => (string) $request->ip(),
            'method' => $request->getMethod(),
            'path' => $request->path(),
            'route_name' => $request->route()?->getName(),
            'payload_hash' => hash('sha256', (string) $request->getContent()),
            'created_at' => now(),
        ]);

        return $response;
    }
}
