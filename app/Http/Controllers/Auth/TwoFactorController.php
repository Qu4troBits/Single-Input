<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Application\Auth\TwoFactor\Challenge\TwoFactorChallengeData;
use App\Application\Auth\TwoFactor\Challenge\TwoFactorChallengeHandler;
use App\Application\Auth\TwoFactor\Confirm\TwoFactorConfirmData;
use App\Application\Auth\TwoFactor\Confirm\TwoFactorConfirmHandler;
use App\Application\Auth\TwoFactor\Setup\TwoFactorSetupData;
use App\Application\Auth\TwoFactor\Setup\TwoFactorSetupHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TwoFactorChallengeRequest;
use App\Http\Requests\Auth\TwoFactorConfirmRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

final class TwoFactorController extends Controller
{
    public function showChallenge(): Response
    {
        return Inertia::render('Auth/TwoFactorChallenge');
    }

    public function challenge(TwoFactorChallengeRequest $request, TwoFactorChallengeHandler $handler): RedirectResponse
    {
        try {
            $handler->handle(new TwoFactorChallengeData(
                code: (string) $request->string('code'),
            ));
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'code' => $e->getMessage(),
            ]);
        }

        return redirect()->route('dashboard');
    }

    public function showSetup(TwoFactorSetupHandler $handler): Response
    {
        $user = request()->user();

        if ($user === null) {
            abort(401);
        }

        $result = $handler->handle(new TwoFactorSetupData(
            userId: (int) $user->getAuthIdentifier(),
            issuer: config('app.name'),
            accountName: (string) $user->getAttribute('email'),
        ));

        return Inertia::render('Auth/TwoFactorSetup', [
            'secret' => $result->secret,
            'otpAuthUri' => $result->otpAuthUri,
        ]);
    }

    public function confirm(TwoFactorConfirmRequest $request, TwoFactorConfirmHandler $handler): RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            abort(401);
        }

        try {
            $handler->handle(new TwoFactorConfirmData(
                userId: (int) $user->getAuthIdentifier(),
                code: (string) $request->string('code'),
            ));
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'code' => $e->getMessage(),
            ]);
        }

        return redirect()->route('dashboard');
    }
}
