<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PasswordPolicyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string}>
     */
    public static function weakPasswords(): array
    {
        return [
            'too short' => ['Aa1!aaaa'],
            'no uppercase letter' => ['lowercase-only-1!'],
            'no lowercase letter' => ['UPPERCASE-ONLY-1!'],
            'no number' => ['No-Numbers-Here!'],
            'no symbol' => ['NoSymbolsHere123'],
        ];
    }

    #[DataProvider('weakPasswords')]
    public function test_registration_rejects_weak_passwords(string $password): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => $password,
            'password_confirmation' => $password,
        ])->assertSessionHasErrors('password');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    #[DataProvider('weakPasswords')]
    public function test_password_update_rejects_weak_passwords(string $password): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/password', [
            'current_password' => 'password',
            'password' => $password,
            'password_confirmation' => $password,
        ])->assertSessionHasErrorsIn('updatePassword', 'password');
    }

    public function test_passwords_are_stored_hashed(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Str0ng-Passphrase!',
            'password_confirmation' => 'Str0ng-Passphrase!',
        ])->assertSessionHasNoErrors();

        $stored = User::where('email', 'test@example.com')->firstOrFail()->password;

        $this->assertNotSame('Str0ng-Passphrase!', $stored);
        $this->assertTrue(password_verify('Str0ng-Passphrase!', $stored));
    }
}
