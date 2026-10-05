<?php

namespace App\Listeners;

use App\Enums\SecurityEventType;
use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Str;
use Laravel\Fortify\Events\RecoveryCodeReplaced;
use Laravel\Fortify\Events\RecoveryCodesGenerated;
use Laravel\Fortify\Events\TwoFactorAuthenticationConfirmed;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;
use Laravel\Fortify\Events\TwoFactorAuthenticationFailed;
use Laravel\Fortify\Fortify;

class RecordSecurityEvents
{
    /**
     * Framework and Fortify events that map straight onto an audit entry
     * for the user carried by the event.
     *
     * @var array<class-string, SecurityEventType>
     */
    protected const USER_EVENTS = [
        Login::class => SecurityEventType::LoginSucceeded,
        Logout::class => SecurityEventType::LoggedOut,
        Registered::class => SecurityEventType::Registered,
        Verified::class => SecurityEventType::EmailVerified,
        PasswordReset::class => SecurityEventType::PasswordReset,
        TwoFactorAuthenticationConfirmed::class => SecurityEventType::TwoFactorEnabled,
        TwoFactorAuthenticationDisabled::class => SecurityEventType::TwoFactorDisabled,
        TwoFactorAuthenticationFailed::class => SecurityEventType::TwoFactorFailed,
        RecoveryCodesGenerated::class => SecurityEventType::RecoveryCodesGenerated,
        RecoveryCodeReplaced::class => SecurityEventType::RecoveryCodeUsed,
    ];

    public function handleUserEvent(object $event): void
    {
        $user = $event->user ?? null;

        // A logout with an already expired session carries no user.
        if (! $user instanceof User) {
            return;
        }

        SecurityEvent::record(self::USER_EVENTS[$event::class], $user);
    }

    public function handleFailedLogin(Failed $event): void
    {
        // The submitted identifier is stored, the submitted password never is.
        SecurityEvent::record(
            SecurityEventType::LoginFailed,
            $event->user instanceof User ? $event->user : null,
            ['email' => Str::limit((string) ($event->credentials[Fortify::username()] ?? ''), 255, '')],
        );
    }

    public function handleLockout(Lockout $event): void
    {
        SecurityEvent::record(
            SecurityEventType::LockedOut,
            null,
            ['email' => Str::limit((string) $event->request->input(Fortify::username()), 255, '')],
        );
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @return array<class-string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            ...array_fill_keys(array_keys(self::USER_EVENTS), 'handleUserEvent'),
            Failed::class => 'handleFailedLogin',
            Lockout::class => 'handleLockout',
        ];
    }
}
