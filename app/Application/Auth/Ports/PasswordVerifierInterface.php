<?php

declare(strict_types=1);

namespace App\Application\Auth\Ports;

interface PasswordVerifierInterface
{
    public function verify(string $plain, string $hash): bool;
}
