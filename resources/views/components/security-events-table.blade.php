@props(['events', 'showUser' => false])

@if ($events->isEmpty())
    <p class="text-sm text-gray-500">{{ __('No security events recorded yet.') }}</p>
@else
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead>
                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <th scope="col" class="py-2 pe-4">{{ __('When') }}</th>
                    <th scope="col" class="py-2 pe-4">{{ __('Event') }}</th>
                    @if ($showUser)
                        <th scope="col" class="py-2 pe-4">{{ __('Account') }}</th>
                    @endif
                    <th scope="col" class="py-2 pe-4">{{ __('IP address') }}</th>
                    <th scope="col" class="py-2">{{ __('Browser') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($events as $event)
                    <tr>
                        <td class="whitespace-nowrap py-2 pe-4 text-gray-600" title="{{ $event->created_at->toDayDateTimeString() }}">
                            {{ $event->created_at->diffForHumans() }}
                        </td>
                        <td class="py-2 pe-4">
                            <x-status-badge :color="$event->type->color()">{{ __($event->type->label()) }}</x-status-badge>
                        </td>
                        @if ($showUser)
                            <td class="py-2 pe-4 text-gray-900">
                                {{ $event->user?->email ?? ($event->metadata['email'] ?? __('Unknown')) }}
                                @if (! $event->user)
                                    <span class="text-xs text-gray-500">({{ __('no account') }})</span>
                                @endif
                            </td>
                        @endif
                        <td class="whitespace-nowrap py-2 pe-4 font-mono text-xs text-gray-600">{{ $event->ip_address ?? '-' }}</td>
                        <td class="max-w-xs truncate py-2 text-xs text-gray-500" title="{{ $event->user_agent }}">{{ $event->user_agent ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
