<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Application\Auth\Ports\TwoFactorSecretCipherInterface;
use Illuminate\Support\Facades\Crypt;

final readonly class LaravelTwoFactorSecretCipher implements TwoFactorSecretCipherInterface
{
    public function encrypt(string $plain): string
    {
        return Crypt::encryptString($plain);
    }

    public function decrypt(string $ciphertext): string
    {
        return Crypt::decryptString($ciphertext);
    }
}
