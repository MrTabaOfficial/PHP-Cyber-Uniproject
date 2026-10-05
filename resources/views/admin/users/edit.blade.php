<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="{{ route('admin.users.index') }}" class="text-gray-500 hover:text-gray-700">{{ __('Users') }}</a>
            <span class="text-gray-400">/</span>
            {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow-sm sm:rounded-lg">
                <section class="max-w-xl">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">{{ __('Account details') }}</h2>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('Changing the email address requires the user to verify the new address.') }}
                        </p>
                    </header>

                    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="mt-6 space-y-6">
                        @csrf
                        @method('patch')

                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>

                        <div>
                            <x-input-label for="role" :value="__('Role')" />
                            @can('changeRole', $user)
                                <select id="role" name="role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-xs">
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->value }}" @selected(old('role', $user->role->value) === $role->value)>{{ __($role->label()) }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="hidden" name="role" value="{{ $user->role->value }}">
                                <p id="role" class="mt-1 text-sm text-gray-600">
                                    {{ __($user->role->label()) }}. {{ __('You cannot change your own role.') }}
                                </p>
                            @endcan
                            <x-input-error class="mt-2" :messages="$errors->get('role')" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Save') }}</x-primary-button>

                            @if (session('status') === 'user-updated')
                                <p class="text-sm text-gray-600" role="status">{{ __('Saved.') }}</p>
                            @endif
                        </div>
                    </form>
                </section>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow-sm sm:rounded-lg">
                <section>
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">{{ __('Recent account activity') }}</h2>
                    </header>

                    <div class="mt-4">
                        <x-security-events-table :events="$events" />
                    </div>
                </section>
            </div>

            @can('delete', $user)
                <div class="p-4 sm:p-8 bg-white shadow-sm sm:rounded-lg">
                    <section class="max-w-xl space-y-6">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">{{ __('Delete user') }}</h2>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('The account is removed permanently. Its audit log entries are kept.') }}
                            </p>
                        </header>

                        <x-danger-button
                            type="button"
                            x-data=""
                            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                        >{{ __('Delete user') }}</x-danger-button>

                        <x-modal name="confirm-user-deletion" focusable>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="p-6">
                                @csrf
                                @method('delete')

                                <h2 class="text-lg font-medium text-gray-900">
                                    {{ __('Delete :name?', ['name' => $user->name]) }}
                                </h2>

                                <p class="mt-1 text-sm text-gray-600">
                                    {{ __('This cannot be undone. :email will no longer be able to sign in.', ['email' => $user->email]) }}
                                </p>

                                <div class="mt-6 flex justify-end">
                                    <x-secondary-button x-on:click="$dispatch('close')">
                                        {{ __('Keep user') }}
                                    </x-secondary-button>

                                    <x-danger-button class="ms-3">
                                        {{ __('Delete user') }}
                                    </x-danger-button>
                                </div>
                            </form>
                        </x-modal>
                    </section>
                </div>
            @endcan
        </div>
    </div>
</x-app-layout>
