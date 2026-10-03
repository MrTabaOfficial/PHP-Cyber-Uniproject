<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Audit log') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="GET" action="{{ route('admin.audit-log') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end">
                        <div class="flex-1">
                            <x-input-label for="q" :value="__('Search by email or IP address')" />
                            <x-text-input id="q" name="q" type="search" class="mt-1 block w-full" :value="$filters['q'] ?? ''" />
                        </div>

                        <div>
                            <x-input-label for="type" :value="__('Event')" />
                            <select id="type" name="type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">{{ __('All events') }}</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->value }}" @selected(($filters['type'] ?? null) === $type->value)>{{ __($type->label()) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <x-primary-button class="h-10 justify-center">{{ __('Filter') }}</x-primary-button>
                    </form>

                    <div class="mt-6">
                        <x-security-events-table :events="$events" show-user />
                    </div>

                    <div class="mt-6">
                        {{ $events->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
