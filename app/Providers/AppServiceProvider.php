<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // One policy for registration, password change and password reset.
        Password::defaults(function () {
            $rule = Password::min(12)->letters()->mixedCase()->numbers()->symbols();

            // Checks the password against known data breaches (haveibeenpwned.com,
            // k-anonymity range API). Skipped outside production to keep local
            // development and tests offline.
            return $this->app->isProduction() ? $rule->uncompromised() : $rule;
        });
    }
}
