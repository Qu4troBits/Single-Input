<?php

declare(strict_types=1);

namespace App\Application\Auth\TwoFactor\Setup;

final readonly class TwoFactorSetupResult
{
    public function __construct(
        public string $secret,
        public string $otpAuthUri,
    ) {
    }
}
