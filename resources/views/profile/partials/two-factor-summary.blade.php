<section>
    <header>
        <h2 class="flex items-center gap-3 text-lg font-medium text-gray-900">
            {{ __('Two-factor authentication') }}
            @if ($user->hasEnabledTwoFactorAuthentication())
                <x-status-badge color="green">{{ __('Enabled') }}</x-status-badge>
            @else
                <x-status-badge color="red">{{ __('Disabled') }}</x-status-badge>
            @endif
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Protect your account with a code from an authenticator app in addition to your password.') }}
        </p>
    </header>

    <p class="mt-6">
        <a href="{{ route('two-factor.show') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            {{ $user->hasEnabledTwoFactorAuthentication() ? __('Manage two-factor') : __('Set up two-factor') }}
        </a>
    </p>

    <p class="mt-3 text-sm text-gray-600">
        {{ __('You will be asked to confirm your password first.') }}
    </p>
</section>
