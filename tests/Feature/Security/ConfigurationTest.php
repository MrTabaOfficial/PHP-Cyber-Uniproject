<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_secret_has_a_default_value_in_the_repository(): void
    {
        $example = file_get_contents(base_path('.env.example'));

        $this->assertMatchesRegularExpression('/^APP_KEY=$/m', $example);
        $this->assertStringContainsString("'key' => env('APP_KEY')", file_get_contents(config_path('app.php')));
        $this->assertStringContainsString("\n.env\n", str_replace("\r", '', file_get_contents(base_path('.gitignore'))));
    }

    public function test_session_cookie_is_hardened(): void
    {
        $this->assertTrue(config('session.http_only'));
        $this->assertSame('lax', config('session.same_site'));
        $this->assertTrue(config('session.encrypt'));
    }

    public function test_password_confirmation_expires_after_fifteen_minutes(): void
    {
        $user = User::factory()->create();

        $this->assertSame(900, config('auth.password_timeout'));

        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time() - 901])
            ->get('/profile/two-factor')
            ->assertRedirect('/user/confirm-password');
    }

    public function test_two_factor_secret_and_recovery_codes_are_encrypted_at_rest(): void
    {
        $user = User::factory()->withTwoFactor()->create();
        $row = DB::table('users')->where('id', $user->id)->first();

        $secret = decrypt($user->two_factor_secret);

        $this->assertStringNotContainsString($secret, $row->two_factor_secret);
        $this->assertStringNotContainsString($user->recoveryCodes()[0], $row->two_factor_recovery_codes);
        $this->assertStringStartsWith('$2y$', $row->password);
    }

    public function test_requests_for_an_unknown_host_are_rejected_in_production(): void
    {
        config(['app.url' => 'https://uniproject.test']);
        $this->app['env'] = 'production';

        try {
            $this->get('https://uniproject.test/login')->assertOk();
            $this->get('https://evil.example/login')->assertStatus(400);
        } finally {
            // Trusted hosts are static on the request class and would leak into other tests.
            Request::setTrustedHosts([]);
        }
    }

    public function test_no_api_routes_are_exposed(): void
    {
        $this->get('/api/user')->assertNotFound();
    }
}
