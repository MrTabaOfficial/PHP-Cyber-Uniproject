<?php

namespace Tests\Feature\Security;

use App\Enums\SecurityEventType;
use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_requires_a_confirmed_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/profile/two-factor')->assertRedirect('/user/confirm-password');

        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()])
            ->get('/profile/two-factor')
            ->assertOk()
            ->assertSee('Set up two-factor');
    }

    public function test_enabling_requires_a_confirmed_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/user/two-factor-authentication')->assertRedirect('/user/confirm-password');

        $this->assertNull($user->fresh()->two_factor_secret);
    }

    public function test_two_factor_is_not_active_until_a_code_is_confirmed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()])
            ->post('/user/two-factor-authentication');

        $user->refresh();

        $this->assertNotNull($user->two_factor_secret);
        $this->assertFalse($user->hasEnabledTwoFactorAuthentication());

        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()])
            ->get('/profile/two-factor')
            ->assertOk()
            ->assertSee('Setup not finished')
            ->assertSee(decrypt($user->two_factor_secret));

        $this->post('/logout');

        // An unfinished setup must not lock the user out of the account.
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_setup_rejects_a_wrong_code_and_accepts_the_right_one(): void
    {
        $user = User::factory()->create();
        $session = ['auth.password_confirmed_at' => time()];

        $this->actingAs($user)->withSession($session)->post('/user/two-factor-authentication');

        $this->actingAs($user)->withSession($session)
            ->post('/user/confirmed-two-factor-authentication', ['code' => '000000'])
            ->assertSessionHasErrorsIn('confirmTwoFactorAuthentication', 'code');

        $this->assertFalse($user->fresh()->hasEnabledTwoFactorAuthentication());

        $this->actingAs($user)->withSession($session)
            ->post('/user/confirmed-two-factor-authentication', ['code' => $this->currentCodeFor($user->fresh())])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'two-factor-authentication-confirmed');

        $user->refresh();

        $this->assertTrue($user->hasEnabledTwoFactorAuthentication());
        $this->assertSame(1, $user->securityEvents()->where('type', SecurityEventType::TwoFactorEnabled)->count());
    }

    public function test_recovery_codes_are_shown_once_after_setup(): void
    {
        $user = User::factory()->withTwoFactor()->create();
        $code = $user->recoveryCodes()[0];

        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time(), 'status' => 'two-factor-authentication-confirmed'])
            ->get('/profile/two-factor')
            ->assertSee($code);

        // The status is a flash message, so the next visit no longer carries it.
        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time(), 'status' => null])
            ->get('/profile/two-factor')
            ->assertOk()
            ->assertDontSee($code);
    }

    public function test_login_with_two_factor_enabled_stops_at_the_challenge(): void
    {
        $user = User::factory()->withTwoFactor()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/two-factor-challenge');

        $this->assertGuest();
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_challenge_accepts_a_valid_code(): void
    {
        $user = User::factory()->withTwoFactor()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->post('/two-factor-challenge', ['code' => $this->currentCodeFor($user)])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_challenge_rejects_an_invalid_code_and_records_it(): void
    {
        $user = User::factory()->withTwoFactor()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->post('/two-factor-challenge', ['code' => '000000'])->assertSessionHasErrors('code');

        $this->assertGuest();
        $this->assertSame(1, $user->securityEvents()->where('type', SecurityEventType::TwoFactorFailed)->count());
    }

    public function test_challenge_cannot_be_reached_without_passing_the_password_step(): void
    {
        $user = User::factory()->withTwoFactor()->create();

        $this->post('/two-factor-challenge', ['code' => $this->currentCodeFor($user)]);

        $this->assertGuest();
    }

    public function test_a_recovery_code_works_exactly_once(): void
    {
        $user = User::factory()->withTwoFactor()->create();
        $recoveryCode = $user->recoveryCodes()[0];

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->post('/two-factor-challenge', ['recovery_code' => $recoveryCode])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
        $this->assertNotContains($recoveryCode, $user->fresh()->recoveryCodes());
        $this->assertSame(1, SecurityEvent::where('type', SecurityEventType::RecoveryCodeUsed)->count());

        $this->post('/logout');
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->post('/two-factor-challenge', ['recovery_code' => $recoveryCode])->assertSessionHasErrors('recovery_code');

        $this->assertGuest();
    }

    public function test_two_factor_can_be_turned_off(): void
    {
        $user = User::factory()->withTwoFactor()->create();

        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()])
            ->delete('/user/two-factor-authentication')
            ->assertSessionHas('status', 'two-factor-authentication-disabled');

        $user->refresh();

        $this->assertFalse($user->hasEnabledTwoFactorAuthentication());
        $this->assertNull($user->two_factor_secret);
        $this->assertSame(1, $user->securityEvents()->where('type', SecurityEventType::TwoFactorDisabled)->count());
    }

    public function test_admins_without_two_factor_are_sent_to_setup(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/users')->assertRedirect('/profile/two-factor');

        $this->actingAs($admin)->withSession(['auth.password_confirmed_at' => time()])
            ->get('/profile/two-factor')
            ->assertSee('Administrators must turn on two-factor authentication');

        config(['security.admin_requires_two_factor' => false]);

        $this->actingAs($admin)->get('/admin/users')->assertOk();
    }

    public function test_two_factor_secrets_are_not_serialized(): void
    {
        $user = User::factory()->withTwoFactor()->create();

        $this->assertArrayNotHasKey('two_factor_secret', $user->toArray());
        $this->assertArrayNotHasKey('two_factor_recovery_codes', $user->toArray());
    }

    protected function currentCodeFor(User $user): string
    {
        return app(Google2FA::class)->getCurrentOtp(decrypt($user->two_factor_secret));
    }
}
