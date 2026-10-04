<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_get_the_user_role(): void
    {
        $user = User::factory()->create();

        $this->assertSame(Role::User, $user->fresh()->role);
        $this->assertFalse($user->isAdmin());
    }

    public function test_role_cannot_be_mass_assigned(): void
    {
        $user = User::factory()->create();

        $user->fill(['role' => Role::Admin->value])->save();

        $this->assertSame(Role::User, $user->fresh()->role);
    }

    public function test_registration_ignores_a_submitted_role(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Str0ng-Passphrase!',
            'password_confirmation' => 'Str0ng-Passphrase!',
            'role' => 'admin',
        ]);

        $this->assertSame(Role::User, User::where('email', 'test@example.com')->firstOrFail()->role);
    }

    public function test_dashboard_is_displayed_for_users_and_admins(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('User');

        $this->actingAs(User::factory()->admin()->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Administrator');
    }

    public function test_guests_are_redirected_from_the_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }
}
