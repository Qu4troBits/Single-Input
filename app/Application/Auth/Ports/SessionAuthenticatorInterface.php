<?php

declare(strict_types=1);

namespace App\Application\Auth\Ports;

interface SessionAuthenticatorInterface
{
    public function loginById(int $userId): void;

    public function logout(): void;
}
