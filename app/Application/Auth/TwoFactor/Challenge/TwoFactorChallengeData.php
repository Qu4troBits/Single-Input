<?php

declare(strict_types=1);

namespace App\Application\Auth\TwoFactor\Challenge;

final readonly class TwoFactorChallengeData
{
    public function __construct(public string $code)
    {
    }
}
