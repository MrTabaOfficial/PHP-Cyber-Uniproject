<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100 text-gray-900">
        <div class="min-h-screen">
            <header class="max-w-5xl mx-auto flex items-center justify-between px-6 py-6">
                <a href="{{ url('/') }}" class="flex items-center gap-3 font-semibold">
                    <x-application-logo class="h-8 w-8 fill-current text-gray-800" />
                    {{ config('app.name', 'Laravel') }}
                </a>

                <nav class="flex items-center gap-4 text-sm font-medium">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-md px-3 py-2 text-gray-700 hover:text-gray-900 focus:outline-hidden focus:ring-2 focus:ring-indigo-500">{{ __('Dashboard') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-md px-3 py-2 text-gray-700 hover:text-gray-900 focus:outline-hidden focus:ring-2 focus:ring-indigo-500">{{ __('Log in') }}</a>
                        <a href="{{ route('register') }}" class="rounded-md bg-gray-800 px-4 py-2 text-white hover:bg-gray-700 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">{{ __('Register') }}</a>
                    @endauth
                </nav>
            </header>

            <main class="max-w-5xl mx-auto px-6 pb-16">
                <section class="py-12 sm:py-16">
                    <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600">{{ __('University cyber security project') }}</p>
                    <h1 class="mt-3 max-w-3xl text-4xl font-semibold tracking-tight sm:text-5xl">
                        {{ __('Account security, built and tested end to end.') }}
                    </h1>
                    <p class="mt-5 max-w-2xl text-lg text-gray-600">
                        {{ __('A Laravel application that shows how sign-in is hardened in practice: two-factor authentication, role-based access, an audit trail and strict browser security headers.') }}
                    </p>
                </section>

                <section aria-label="{{ __('Features') }}" class="grid gap-6 sm:grid-cols-2">
                    @foreach ([
                        ['Two-factor authentication', 'Authenticator app codes with a confirmed setup step and single-use recovery codes. Required for administrators.'],
                        ['Role-based access control', 'An enum-backed role, a policy and middleware keep the admin area closed to regular users. Roles cannot be mass assigned.'],
                        ['Security audit log', 'Sign-ins, failures, lockouts, password and role changes are recorded with IP address and browser, and shown to the account owner.'],
                        ['Hardened defaults', 'Content Security Policy, anti-framing and no-store headers, login rate limiting, a 12-character password policy and email verification.'],
                    ] as [$title, $text])
                        <div class="rounded-lg bg-white p-6 shadow-xs">
                            <h2 class="text-lg font-medium">{{ __($title) }}</h2>
                            <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ __($text) }}</p>
                        </div>
                    @endforeach
                </section>
            </main>
        </div>
    </body>
</html>
