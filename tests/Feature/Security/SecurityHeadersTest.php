<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_send_security_headers(): void
    {
        foreach (['/', '/login', '/register'] as $uri) {
            $response = $this->get($uri)->assertOk();

            $response->assertHeader('X-Content-Type-Options', 'nosniff');
            $response->assertHeader('X-Frame-Options', 'DENY');
            $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
            $response->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
            $response->assertHeaderMissing('Strict-Transport-Security');
        }
    }

    public function test_content_security_policy_blocks_inline_scripts_and_framing(): void
    {
        $policy = $this->get('/login')->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("default-src 'self'", $policy);
        $this->assertStringContainsString("frame-ancestors 'none'", $policy);
        $this->assertStringContainsString("form-action 'self'", $policy);
        $this->assertStringContainsString("object-src 'none'", $policy);
        $this->assertMatchesRegularExpression("/script-src [^;]*'self'/", $policy);
        $this->assertDoesNotMatchRegularExpression("/script-src [^;]*'unsafe-inline'/", $policy);
    }

    public function test_views_contain_no_inline_scripts_or_event_handlers(): void
    {
        $user = User::factory()->admin()->withTwoFactor()->create();

        $pages = ['/dashboard', '/profile', '/admin/users', "/admin/users/{$user->id}/edit", '/admin/audit-log'];

        foreach ($pages as $uri) {
            $html = $this->actingAs($user)->get($uri)->assertOk()->getContent();

            $this->assertDoesNotMatchRegularExpression('/<script(?![^>]*\bsrc=)[^>]*>/i', $html, "Inline script on {$uri}");
            $this->assertDoesNotMatchRegularExpression('/\son(click|submit|load|change)=/i', $html, "Inline handler on {$uri}");
        }
    }

    public function test_https_responses_send_hsts(): void
    {
        $this->get('https://localhost/login')
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_authenticated_pages_are_not_cached(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/dashboard')->assertOk();

        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }
}
