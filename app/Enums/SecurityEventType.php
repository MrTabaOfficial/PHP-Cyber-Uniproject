<?php

namespace App\Enums;

enum SecurityEventType: string
{
    case LoginSucceeded = 'login.succeeded';
    case LoginFailed = 'login.failed';
    case LockedOut = 'login.locked_out';
    case LoggedOut = 'logout';
    case Registered = 'account.registered';
    case EmailVerified = 'email.verified';
    case PasswordChanged = 'password.changed';
    case PasswordReset = 'password.reset';
    case TwoFactorEnabled = 'two_factor.enabled';
    case TwoFactorDisabled = 'two_factor.disabled';
    case TwoFactorFailed = 'two_factor.failed';
    case RecoveryCodesGenerated = 'recovery_codes.generated';
    case RecoveryCodeUsed = 'recovery_codes.used';
    case AccountDeleted = 'account.deleted';
    case UserUpdatedByAdmin = 'admin.user_updated';
    case RoleChangedByAdmin = 'admin.role_changed';
    case UserDeletedByAdmin = 'admin.user_deleted';

    public function label(): string
    {
        return match ($this) {
            self::LoginSucceeded => 'Signed in',
            self::LoginFailed => 'Failed sign-in attempt',
            self::LockedOut => 'Locked out after repeated failures',
            self::LoggedOut => 'Signed out',
            self::Registered => 'Account created',
            self::EmailVerified => 'Email address verified',
            self::PasswordChanged => 'Password changed',
            self::PasswordReset => 'Password reset by email link',
            self::TwoFactorEnabled => 'Two-factor authentication enabled',
            self::TwoFactorDisabled => 'Two-factor authentication disabled',
            self::TwoFactorFailed => 'Failed two-factor challenge',
            self::RecoveryCodesGenerated => 'Recovery codes generated',
            self::RecoveryCodeUsed => 'Recovery code used to sign in',
            self::AccountDeleted => 'Account deleted by its owner',
            self::UserUpdatedByAdmin => 'User details changed by an administrator',
            self::RoleChangedByAdmin => 'Role changed by an administrator',
            self::UserDeletedByAdmin => 'User deleted by an administrator',
        };
    }

    /**
     * The badge color used when the event is listed.
     */
    public function color(): string
    {
        return match ($this) {
            self::LoginFailed, self::LockedOut, self::TwoFactorFailed => 'red',
            self::TwoFactorDisabled, self::RecoveryCodeUsed, self::PasswordReset,
            self::AccountDeleted, self::UserDeletedByAdmin, self::RoleChangedByAdmin => 'yellow',
            self::TwoFactorEnabled, self::EmailVerified => 'green',
            default => 'gray',
        };
    }
}
