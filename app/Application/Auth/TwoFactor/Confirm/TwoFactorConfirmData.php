<?php

declare(strict_types=1);

namespace App\Application\Auth\TwoFactor\Confirm;

final readonly class TwoFactorConfirmData
{
    public function __construct(
        public int $userId,
        public string $code,
    ) {
    }
}
