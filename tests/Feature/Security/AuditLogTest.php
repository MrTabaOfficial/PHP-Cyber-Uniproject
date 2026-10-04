<?php

namespace Tests\Feature\Security;

use App\Enums\SecurityEventType;
use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_login_is_recorded(): void
    {
        $user = User::factory()->create();

        $this->withHeader('User-Agent', 'AuditLogTest')->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $event = SecurityEvent::sole();

        $this->assertSame(SecurityEventType::LoginSucceeded, $event->type);
        $this->assertTrue($event->user->is($user));
        $this->assertSame('127.0.0.1', $event->ip_address);
        $this->assertSame('AuditLogTest', $event->user_agent);
    }

    public function test_failed_login_is_recorded_without_the_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $event = SecurityEvent::sole();

        $this->assertSame(SecurityEventType::LoginFailed, $event->type);
        $this->assertSame(['email' => $user->email], $event->metadata);
        $this->assertStringNotContainsString('wrong-password', json_encode($event->getAttributes()));
    }

    public function test_failed_login_for_an_unknown_account_is_recorded(): void
    {
        $this->post('/login', [
            'email' => 'nobody@example.com',
            'password' => 'wrong-password',
        ]);

        $event = SecurityEvent::sole();

        $this->assertSame(SecurityEventType::LoginFailed, $event->type);
        $this->assertNull($event->user_id);
        $this->assertSame('nobody@example.com', $event->metadata['email']);
    }

    public function test_lockout_is_recorded_after_too_many_attempts(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 6) as $attempt) {
            $response = $this->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $response->assertSessionHasErrors('email');
        $this->assertSame(5, SecurityEvent::where('type', SecurityEventType::LoginFailed)->count());
        $this->assertSame(1, SecurityEvent::where('type', SecurityEventType::LockedOut)->count());
    }

    public function test_logout_and_password_change_are_recorded(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/password', [
            'current_password' => 'password',
            'password' => 'Str0ng-Passphrase!',
            'password_confirmation' => 'Str0ng-Passphrase!',
        ]);

        $this->actingAs($user)->post('/logout');

        $this->assertSame(
            [SecurityEventType::PasswordChanged, SecurityEventType::LoggedOut],
            $user->securityEvents()->oldest('id')->pluck('type')->all(),
        );
    }

    public function test_events_survive_account_deletion(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->delete('/profile', ['password' => 'password']);

        $this->assertModelMissing($user);

        $event = SecurityEvent::where('type', SecurityEventType::AccountDeleted)->sole();

        $this->assertNull($event->user_id);
        $this->assertSame($user->email, $event->metadata['email']);
    }

    public function test_dashboard_lists_only_the_users_own_events(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        SecurityEvent::create(['user_id' => $user->id, 'type' => SecurityEventType::PasswordChanged, 'ip_address' => '203.0.113.7']);
        SecurityEvent::create(['user_id' => $other->id, 'type' => SecurityEventType::TwoFactorDisabled, 'ip_address' => '198.51.100.9']);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Password changed')
            ->assertSee('203.0.113.7')
            ->assertDontSee('198.51.100.9');
    }

    public function test_old_events_are_pruned(): void
    {
        config(['security.audit_log.retention_days' => 30]);

        $old = SecurityEvent::create(['type' => SecurityEventType::LoginFailed]);
        $old->forceFill(['created_at' => now()->subDays(31)])->save();
        $recent = SecurityEvent::create(['type' => SecurityEventType::LoginFailed]);

        $this->artisan('model:prune', ['--model' => [SecurityEvent::class]]);

        $this->assertModelMissing($old);
        $this->assertModelExists($recent);
    }
}
