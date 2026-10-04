<x-guest-layout>
    <form method="POST" action="{{ route('two-factor.login.store') }}" x-data="{ recovery: {{ $errors->has('recovery_code') ? 'true' : 'false' }} }">
        @csrf

        <div x-show="! recovery">
            <p class="mb-4 text-sm text-gray-600">
                {{ __('Enter the 6-digit code from your authenticator app to finish signing in.') }}
            </p>

            <x-input-label for="code" :value="__('Authentication code')" />
            <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" autofocus x-bind:disabled="recovery" />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div x-show="recovery" x-cloak>
            <p class="mb-4 text-sm text-gray-600">
                {{ __('Enter one of the recovery codes you saved when you set up two-factor authentication. Each code works once.') }}
            </p>

            <x-input-label for="recovery_code" :value="__('Recovery code')" />
            <x-text-input id="recovery_code" class="block mt-1 w-full" type="text" name="recovery_code" autocomplete="off" x-bind:disabled="! recovery" />
            <x-input-error :messages="$errors->get('recovery_code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <button type="button" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" x-on:click="recovery = ! recovery">
                <span x-show="! recovery">{{ __('Use a recovery code') }}</span>
                <span x-show="recovery" x-cloak>{{ __('Use an authentication code') }}</span>
            </button>

            <x-primary-button>
                {{ __('Sign in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
