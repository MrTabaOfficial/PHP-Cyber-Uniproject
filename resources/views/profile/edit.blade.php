<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    Enable 2FA Auth
                    @if(session('status') == 'two-factor-authentication-enabled')
                        <div class="mb-4 font-medium text-sm">
                            Please Finish two Factor Authentication
                            First of all you Must Download Application From Google Play Or App Store Named: Google Authenticator
                            After this you must scan this QR code And Thats all
                        </div>
                    @endif
                    @if(session('status') == 'two-factor-authentication-disabled')
                        <div class="mb-4 font-medium text-sm">
                            Two Factor Authentication Disabled
                        </div>
                    @endif
                    @if(session('status') == 'two-factor-authentication-confirmed')
                        <div class="mb-4 font-medium text-sm">
                            Two Factor Authentication confirmed and enabled successfully
                        </div>
                    @endif
                    {{-- QR Check --}}
                    @if(auth()->user()->two_factor_secret)
                        <div class="mb-4 font-medium text-sm">
                            {!! auth()->user()->twoFactorQrCodeSvg() !!}
                        </div>
                    @endif
                    {{-- button Activate--}}
                    @if(!auth()->user()->two_factor_secret)
                        <form method="POST" action="/user/two-factor-authentication">
                            @csrf
                            <x-primary-button class="my-2">
                                {{ __('Enabled') }}
                            </x-primary-button>
                        </form>
                        @else
                            <form method="POST" action="/user/two-factor-authentication">
                                @csrf
                                @method('delete')
                                <x-danger-button class="my-2">
                                    {{ __('Disabled') }}
                                </x-danger-button>
                            </form>
                    @endif
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
