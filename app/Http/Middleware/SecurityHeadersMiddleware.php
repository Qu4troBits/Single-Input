<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security Headers Middleware
 * 
 * Implements all security headers as per OWASP recommendations
 * and the architecture requirements defined in single-input-architecture.md
 */
final class SecurityHeadersMiddleware
{
    /**
     * Security header configurations by environment
     */
    private const HEADER_CONFIG = [
        'production' => [
            'strict' => true,
            'report_only' => false,
        ],
        'staging' => [
            'strict' => true,
            'report_only' => true,
        ],
        'local' => [
            'strict' => false,
            'report_only' => true,
        ],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $this->setSecurityHeaders($response, $request);

        return $response;
    }

    private function setSecurityHeaders(Response $response, Request $request): void
    {
        $env = config('app.env', 'production');
        $config = self::HEADER_CONFIG[$env] ?? self::HEADER_CONFIG['production'];

        // 1. X-Frame-Options - Prevents clickjacking
        $this->setHeader($response, 'X-Frame-Options', 'DENY');

        // 2. X-Content-Type-Options - Prevents MIME sniffing
        $this->setHeader($response, 'X-Content-Type-Options', 'nosniff');

        // 3. X-XSS-Protection - Legacy XSS protection (deprecated but still useful)
        $this->setHeader($response, 'X-XSS-Protection', '1; mode=block');

        // 4. Referrer-Policy - Controls referrer information
        $this->setHeader($response, 'Referrer-Policy', 'strict-origin-when-cross-origin');

        // 5. Strict-Transport-Security (HSTS) - Forces HTTPS
        if ($config['strict']) {
            $this->setHeader(
                $response,
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // 6. Content-Security-Policy (CSP) - Prevents XSS and data injection
        $this->setCspHeader($response, $config);

        // 7. Permissions-Policy - Controls browser features
        $this->setPermissionsPolicy($response);

        // 8. Cross-Origin policies
        $this->setCrossOriginPolicies($response, $config);

        // 9. Remove server fingerprinting headers
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');
    }

    private function setCspHeader(Response $response, array $config): void
    {
        $cspDirectives = [
            "default-src 'self'",
            "script-src 'self' 'nonce-{nonce}' 'strict-dynamic'",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: https:",
            "font-src 'self'",
            "connect-src 'self'",
            "media-src 'self'",
            "object-src 'none'",
            "frame-src 'none'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "base-uri 'self'",
            "upgrade-insecure-requests",
        ];

        $csp = implode('; ', $cspDirectives);

        // Replace nonce placeholder with actual nonce
        $nonce = $this->generateNonce();
        $csp = str_replace('{nonce}', $nonce, $csp);

        if ($config['report_only']) {
            $this->setHeader($response, 'Content-Security-Policy-Report-Only', $csp);
        } else {
            $this->setHeader($response, 'Content-Security-Policy', $csp);
        }
    }

    private function setPermissionsPolicy(Response $response): void
    {
        $policies = [
            'accelerometer=()',
            'camera=()',
            'geolocation=()',
            'gyroscope=()',
            'magnetometer=()',
            'microphone=()',
            'payment=()',
            'usb=()',
        ];

        $this->setHeader($response, 'Permissions-Policy', implode(', ', $policies));
    }

    private function setCrossOriginPolicies(Response $response, array $config): void
    {
        // Cross-Origin-Resource-Policy
        $this->setHeader($response, 'Cross-Origin-Resource-Policy', 'same-origin');

        // Cross-Origin-Opener-Policy
        $this->setHeader($response, 'Cross-Origin-Opener-Policy', 'same-origin');

        // Cross-Origin-Embedder-Policy
        if ($config['strict']) {
            $this->setHeader($response, 'Cross-Origin-Embedder-Policy', 'require-corp');
        }
    }

    private function setHeader(Response $response, string $name, string $value): void
    {
        // Prevent duplicate headers
        $response->headers->remove($name);
        $response->headers->set($name, $value);
    }

    private function generateNonce(): string
    {
        return base64_encode(random_bytes(16));
    }
}
