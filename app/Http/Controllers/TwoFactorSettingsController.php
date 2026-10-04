<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Fortify\Fortify;

class TwoFactorSettingsController extends Controller
{
    /**
     * Display the two-factor authentication settings.
     *
     * The route requires a recently confirmed password, so the enable,
     * confirm, regenerate and disable forms on this page (handled by
     * Fortify, which demands the same confirmation) submit without detours.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $status = $request->session()->get('status');

        return view('profile.two-factor', [
            'user' => $user,
            'enabled' => $user->hasEnabledTwoFactorAuthentication(),
            // Derived from state rather than a flash message, which would be lost
            // on the password confirmation detour in front of this page.
            'requiredForAdmin' => $user->isAdmin()
                && config('security.admin_requires_two_factor')
                && ! $user->hasEnabledTwoFactorAuthentication(),
            'pending' => $user->two_factor_secret && ! $user->two_factor_confirmed_at,
            // Recovery codes are displayed once: right after they are created.
            'showRecoveryCodes' => in_array($status, [
                Fortify::TWO_FACTOR_AUTHENTICATION_CONFIRMED,
                Fortify::RECOVERY_CODES_GENERATED,
            ], true),
        ]);
    }
}
