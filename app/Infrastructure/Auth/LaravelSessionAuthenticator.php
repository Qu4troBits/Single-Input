<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Application\Auth\Ports\SessionAuthenticatorInterface;
use Illuminate\Support\Facades\Auth;

final readonly class LaravelSessionAuthenticator implements SessionAuthenticatorInterface
{
    public function loginById(int $userId): void
    {
        Auth::loginUsingId($userId);
        session()->regenerate();
    }

    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
    }
}
