<?php

declare(strict_types=1);

namespace App\Application\Auth\Ports;

interface TwoFactorSecretCipherInterface
{
    public function encrypt(string $plain): string;

    public function decrypt(string $ciphertext): string;
}
