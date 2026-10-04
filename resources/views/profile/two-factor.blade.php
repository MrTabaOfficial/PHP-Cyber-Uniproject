<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="{{ route('profile.edit') }}" class="text-gray-500 hover:text-gray-700">{{ __('Profile') }}</a>
            <span class="text-gray-400">/</span>
            {{ __('Two-factor authentication') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($requiredForAdmin)
                <div class="rounded-md bg-yellow-50 p-4 text-sm text-yellow-800" role="alert">
                    {{ __('Administrators must turn on two-factor authentication before opening the admin area.') }}
                </div>
            @endif

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <section class="max-w-xl">
                    <header>
                        <h2 class="flex items-center gap-3 text-lg font-medium text-gray-900">
                            {{ __('Two-factor authentication') }}
                            @if ($enabled)
                                <x-status-badge color="green">{{ __('Enabled') }}</x-status-badge>
                            @elseif ($pending)
                                <x-status-badge color="yellow">{{ __('Setup not finished') }}</x-status-badge>
                            @else
                                <x-status-badge color="red">{{ __('Disabled') }}</x-status-badge>
                            @endif
                        </h2>

                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('After your password, sign-in asks for a 6-digit code from an authenticator app such as Google Authenticator, Aegis or 1Password.') }}
                        </p>
                    </header>

                    @if ($enabled)
                        @if (session('status') === 'two-factor-authentication-confirmed')
                            <p class="mt-6 text-sm font-medium text-green-700" role="status">
                                {{ __('Two-factor authentication is now on.') }}
                            </p>
                        @endif

                        @if ($showRecoveryCodes)
                            <div class="mt-6">
                                <h3 class="font-medium text-gray-900">{{ __('Save your recovery codes') }}</h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    {{ __('Each code signs you in once if you lose your phone. They are shown only now, so store them in a password manager.') }}
                                </p>

                                <ul class="mt-4 grid grid-cols-1 gap-1 rounded-lg bg-gray-100 p-4 font-mono text-sm sm:grid-cols-2">
                                    @foreach ($user->recoveryCodes() as $code)
                                        <li>{{ $code }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mt-6 flex flex-wrap items-center gap-4">
                            <form method="POST" action="{{ route('two-factor.regenerate-recovery-codes') }}">
                                @csrf
                                <x-secondary-button type="submit">{{ __('Generate new recovery codes') }}</x-secondary-button>
                            </form>

                            <form method="POST" action="{{ route('two-factor.disable') }}">
                                @csrf
                                @method('delete')
                                <x-danger-button>{{ __('Turn off two-factor') }}</x-danger-button>
                            </form>
                        </div>

                        <p class="mt-3 text-sm text-gray-600">
                            {{ __('Generating new recovery codes makes the old ones stop working.') }}
                        </p>
                    @elseif ($pending)
                        <ol class="mt-6 list-decimal space-y-4 ps-5 text-sm text-gray-700">
                            <li>
                                {{ __('Scan this QR code with your authenticator app.') }}
                                <div class="mt-3 w-fit rounded-lg border border-gray-200 bg-white p-3">
                                    {!! $user->twoFactorQrCodeSvg() !!}
                                </div>
                                <p class="mt-3">
                                    {{ __('Cannot scan it? Enter this setup key instead:') }}
                                    <code class="break-all rounded bg-gray-100 px-1.5 py-0.5 font-mono text-gray-900">{{ decrypt($user->two_factor_secret) }}</code>
                                </p>
                            </li>
                            <li>
                                {{ __('Enter the 6-digit code the app shows to finish setup.') }}

                                <form method="POST" action="{{ route('two-factor.confirm') }}" class="mt-3">
                                    @csrf

                                    <x-input-label for="code" :value="__('Authentication code')" />
                                    <x-text-input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" class="mt-1 block w-40" required autofocus />
                                    <x-input-error class="mt-2" :messages="$errors->confirmTwoFactorAuthentication->get('code')" />

                                    <x-primary-button class="mt-4">{{ __('Confirm and turn on') }}</x-primary-button>
                                </form>
                            </li>
                        </ol>

                        <form method="POST" action="{{ route('two-factor.disable') }}" class="mt-6">
                            @csrf
                            @method('delete')
                            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ __('Cancel setup') }}
                            </button>
                        </form>
                    @else
                        @if (session('status') === 'two-factor-authentication-disabled')
                            <p class="mt-6 text-sm font-medium text-gray-700" role="status">
                                {{ __('Two-factor authentication is off.') }}
                            </p>
                        @endif

                        <form method="POST" action="{{ route('two-factor.enable') }}" class="mt-6">
                            @csrf
                            <x-primary-button>{{ __('Set up two-factor') }}</x-primary-button>
                        </form>
                    @endif
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
