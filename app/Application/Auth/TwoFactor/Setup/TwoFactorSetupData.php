<?php

declare(strict_types=1);

namespace App\Application\Auth\TwoFactor\Setup;

final readonly class TwoFactorSetupData
{
    public function __construct(
        public int $userId,
        public string $issuer,
        public string $accountName,
    ) {
    }
}
