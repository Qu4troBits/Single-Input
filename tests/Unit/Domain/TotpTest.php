<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Security\TwoFactor\Totp;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class TotpTest extends TestCase
{
    public function test_it_verifies_code_for_timestamp(): void
    {
        $secret = 'JBSWY3DPEHPK3PXP';
        $timestamp = 1_700_000_000;

        $code = $this->generateCodeForTimestamp($secret, $timestamp);

        self::assertTrue(Totp::verify($secret, $code, window: 0, timestamp: $timestamp));
        self::assertFalse(Totp::verify($secret, '000000', window: 0, timestamp: $timestamp));
    }

    private function generateCodeForTimestamp(string $secret, int $timestamp): string
    {
        $ref = new ReflectionClass(Totp::class);

        $decode = $ref->getMethod('base32Decode');
        $decode->setAccessible(true);

        $generate = $ref->getMethod('generateCode');
        $generate->setAccessible(true);

        $key = (string) $decode->invoke(null, $secret);
        $timeStep = intdiv($timestamp, 30);

        return (string) $generate->invoke(null, $key, $timeStep);
    }
}

