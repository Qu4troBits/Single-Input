<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Application\Auth\Login\LoginData;
use App\Application\Auth\Login\LoginHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

final class LoginController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function store(LoginRequest $request, LoginHandler $handler): RedirectResponse
    {
        try {
            $result = $handler->handle(new LoginData(
                email: (string) $request->string('email'),
                password: (string) $request->string('password'),
            ));
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'email' => $e->getMessage(),
            ]);
        }

        if ($result->twoFactorRequired) {
            return redirect()->route('two-factor.challenge');
        }

        return redirect()->route('dashboard');
    }
}
