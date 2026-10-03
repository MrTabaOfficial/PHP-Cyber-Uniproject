<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Enums\SecurityEventType;
use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/users')->assertRedirect('/login');
        $this->get('/admin/audit-log')->assertRedirect('/login');
    }

    public function test_regular_users_are_forbidden_from_every_admin_route(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($user)->get('/admin/users')->assertForbidden();
        $this->actingAs($user)->get('/admin/audit-log')->assertForbidden();
        $this->actingAs($user)->get("/admin/users/{$target->id}/edit")->assertForbidden();
        $this->actingAs($user)->patch("/admin/users/{$target->id}", ['name' => 'X', 'email' => 'x@example.com', 'role' => 'admin'])->assertForbidden();
        $this->actingAs($user)->delete("/admin/users/{$target->id}")->assertForbidden();

        $this->assertModelExists($target);
        $this->assertSame(Role::User, $target->fresh()->role);
    }

    public function test_users_cannot_promote_themselves(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch("/admin/users/{$user->id}", ['name' => $user->name, 'email' => $user->email, 'role' => 'admin'])
            ->assertForbidden();

        $this->assertSame(Role::User, $user->fresh()->role);
    }

    public function test_unverified_admins_must_verify_their_email_first(): void
    {
        $admin = User::factory()->admin()->unverified()->create();

        $this->actingAs($admin)->get('/admin/users')->assertRedirect('/verify-email');
    }

    public function test_admin_can_list_search_and_filter_users(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Ada Admin']);
        User::factory()->create(['name' => 'Grace Hopper', 'email' => 'grace@example.com']);
        User::factory()->create(['name' => 'Alan Turing', 'email' => 'alan@example.com']);

        $this->actingAs($admin)->get('/admin/users')
            ->assertOk()
            ->assertSee('Grace Hopper')
            ->assertSee('Alan Turing');

        $this->actingAs($admin)->get('/admin/users?q=grace')
            ->assertOk()
            ->assertSee('Grace Hopper')
            ->assertDontSee('Alan Turing');

        $this->actingAs($admin)->get('/admin/users?role=admin')
            ->assertOk()
            ->assertSee('Ada Admin')
            ->assertDontSee('Grace Hopper');
    }

    public function test_search_treats_like_wildcards_as_user_input(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'Grace Hopper']);

        $this->actingAs($admin)->get('/admin/users?q='.urlencode("' OR 1=1 --"))
            ->assertOk()
            ->assertDontSee('Grace Hopper');
    }

    public function test_invalid_filters_are_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/users?role=superuser')->assertSessionHasErrors('role');
    }

    public function test_admin_can_promote_a_user_and_the_change_is_audited(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->patch("/admin/users/{$user->id}", ['name' => $user->name, 'email' => $user->email, 'role' => 'admin'])
            ->assertRedirect("/admin/users/{$user->id}/edit")
            ->assertSessionHasNoErrors();

        $this->assertTrue($user->fresh()->isAdmin());

        $event = SecurityEvent::where('type', SecurityEventType::RoleChangedByAdmin)->sole();

        $this->assertSame($user->id, $event->user_id);
        $this->assertSame(['admin_id' => $admin->id, 'from' => 'user', 'to' => 'admin'], $event->metadata);
    }

    public function test_admin_changing_an_email_resets_verification(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->patch("/admin/users/{$user->id}", ['name' => 'New Name', 'email' => 'new@example.com', 'role' => 'user'])
            ->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertSame('New Name', $user->name);
        $this->assertSame('new@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
        $this->assertSame(1, SecurityEvent::where('type', SecurityEventType::UserUpdatedByAdmin)->count());
    }

    public function test_email_must_stay_unique_and_role_must_be_valid(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->patch("/admin/users/{$user->id}", ['name' => $user->name, 'email' => $admin->email, 'role' => 'superuser'])
            ->assertSessionHasErrors(['email', 'role']);
    }

    public function test_admin_cannot_change_their_own_role(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->patch("/admin/users/{$admin->id}", ['name' => $admin->name, 'email' => $admin->email, 'role' => 'user'])
            ->assertForbidden();

        $this->assertTrue($admin->fresh()->isAdmin());
    }

    public function test_admin_can_delete_a_user_and_the_deletion_is_audited(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->delete("/admin/users/{$user->id}")
            ->assertRedirect('/admin/users');

        $this->assertModelMissing($user);

        $event = SecurityEvent::where('type', SecurityEventType::UserDeletedByAdmin)->sole();

        $this->assertSame(['admin_id' => $admin->id, 'email' => $user->email], $event->metadata);
    }

    public function test_admin_cannot_delete_their_own_account_from_the_admin_panel(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->delete("/admin/users/{$admin->id}")->assertForbidden();

        $this->assertModelExists($admin);
    }

    public function test_the_only_admin_cannot_delete_their_account_from_the_profile(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from('/profile')
            ->delete('/profile', ['password' => 'password'])
            ->assertSessionHasErrorsIn('userDeletion', 'password');

        $this->assertModelExists($admin);
    }

    public function test_admin_can_filter_the_audit_log(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['email' => 'grace@example.com']);

        SecurityEvent::create(['user_id' => $user->id, 'type' => SecurityEventType::LoginFailed, 'ip_address' => '203.0.113.7']);
        SecurityEvent::create(['user_id' => $admin->id, 'type' => SecurityEventType::PasswordChanged, 'ip_address' => '198.51.100.9']);

        $this->actingAs($admin)->get('/admin/audit-log')
            ->assertOk()
            ->assertSee('203.0.113.7')
            ->assertSee('198.51.100.9');

        $this->actingAs($admin)->get('/admin/audit-log?type=login.failed')
            ->assertOk()
            ->assertSee('203.0.113.7')
            ->assertDontSee('198.51.100.9');

        $this->actingAs($admin)->get('/admin/audit-log?q=grace')
            ->assertOk()
            ->assertSee('203.0.113.7')
            ->assertDontSee('198.51.100.9');
    }
}
