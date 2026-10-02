<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium">{{ __('Welcome back, :name', ['name' => $user->name]) }}</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Signed in as :role.', ['role' => $user->role->label()]) }}
                    </p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium">{{ __('Account security') }}</h3>

                    <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="rounded-lg border border-gray-200 p-4">
                            <dt class="text-sm text-gray-500">{{ __('Email address') }}</dt>
                            <dd class="mt-1">
                                @if ($user->hasVerifiedEmail())
                                    <x-status-badge color="green">{{ __('Verified') }}</x-status-badge>
                                @else
                                    <x-status-badge color="yellow">{{ __('Not verified') }}</x-status-badge>
                                @endif
                            </dd>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4">
                            <dt class="text-sm text-gray-500">{{ __('Two-factor authentication') }}</dt>
                            <dd class="mt-1">
                                @if ($user->hasEnabledTwoFactorAuthentication())
                                    <x-status-badge color="green">{{ __('Enabled') }}</x-status-badge>
                                @else
                                    <x-status-badge color="red">{{ __('Disabled') }}</x-status-badge>
                                @endif
                            </dd>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4">
                            <dt class="text-sm text-gray-500">{{ __('Role') }}</dt>
                            <dd class="mt-1">
                                <x-status-badge :color="$user->isAdmin() ? 'indigo' : 'gray'">{{ $user->role->label() }}</x-status-badge>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium">{{ __('Recent account activity') }}</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('The last 10 security events on your account. Change your password if you do not recognize one.') }}
                    </p>

                    <div class="mt-4">
                        <x-security-events-table :events="$events" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
