<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Users') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status') === 'user-deleted')
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800" role="status">
                    {{ __('The user was deleted.') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end">
                        <div class="flex-1">
                            <x-input-label for="q" :value="__('Search by name or email')" />
                            <x-text-input id="q" name="q" type="search" class="mt-1 block w-full" :value="$filters['q'] ?? ''" />
                        </div>

                        <div>
                            <x-input-label for="role" :value="__('Role')" />
                            <select id="role" name="role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">{{ __('All roles') }}</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->value }}" @selected(($filters['role'] ?? null) === $role->value)>{{ __($role->label()) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <x-primary-button class="h-10 justify-center">{{ __('Filter') }}</x-primary-button>
                    </form>

                    <div class="mt-6 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    <th scope="col" class="py-2 pe-4">{{ __('Name') }}</th>
                                    <th scope="col" class="py-2 pe-4">{{ __('Email') }}</th>
                                    <th scope="col" class="py-2 pe-4">{{ __('Role') }}</th>
                                    <th scope="col" class="py-2 pe-4">{{ __('Two-factor') }}</th>
                                    <th scope="col" class="py-2 pe-4">{{ __('Joined') }}</th>
                                    <th scope="col" class="py-2"><span class="sr-only">{{ __('Actions') }}</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($users as $user)
                                    <tr>
                                        <td class="py-3 pe-4 font-medium text-gray-900">
                                            {{ $user->name }}
                                            @if ($user->is(auth()->user()))
                                                <span class="text-xs font-normal text-gray-500">({{ __('you') }})</span>
                                            @endif
                                        </td>
                                        <td class="py-3 pe-4 text-gray-600">
                                            {{ $user->email }}
                                            @unless ($user->hasVerifiedEmail())
                                                <x-status-badge color="yellow" class="ms-1">{{ __('Not verified') }}</x-status-badge>
                                            @endunless
                                        </td>
                                        <td class="py-3 pe-4">
                                            <x-status-badge :color="$user->isAdmin() ? 'indigo' : 'gray'">{{ __($user->role->label()) }}</x-status-badge>
                                        </td>
                                        <td class="py-3 pe-4">
                                            @if ($user->hasEnabledTwoFactorAuthentication())
                                                <x-status-badge color="green">{{ __('Enabled') }}</x-status-badge>
                                            @else
                                                <x-status-badge color="red">{{ __('Disabled') }}</x-status-badge>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap py-3 pe-4 text-gray-600">{{ $user->created_at->toFormattedDateString() }}</td>
                                        <td class="py-3 text-end">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="font-medium text-indigo-600 hover:text-indigo-900 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                                {{ __('Manage') }}<span class="sr-only">, {{ $user->name }}</span>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-6 text-center text-gray-500">{{ __('No users match these filters.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
