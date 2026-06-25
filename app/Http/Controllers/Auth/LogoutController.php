<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Application\Auth\Ports\SessionAuthenticatorInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

final class LogoutController extends Controller
{
    public function __invoke(SessionAuthenticatorInterface $authenticator): RedirectResponse
    {
        $authenticator->logout();

        return redirect()->route('login');
    }
}
