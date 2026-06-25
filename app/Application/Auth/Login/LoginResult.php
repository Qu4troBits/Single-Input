<?php

declare(strict_types=1);

namespace App\Application\Auth\Login;

final readonly class LoginResult
{
    public function __construct(public bool $twoFactorRequired)
    {
    }
}
