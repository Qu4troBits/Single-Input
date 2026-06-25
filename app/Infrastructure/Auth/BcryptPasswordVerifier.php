<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Application\Auth\Ports\PasswordVerifierInterface;
use Illuminate\Support\Facades\Hash;

final readonly class BcryptPasswordVerifier implements PasswordVerifierInterface
{
    public function verify(string $plain, string $hash): bool
    {
        return Hash::check($plain, $hash);
    }
}
