<x-guest-layout>
    <div class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center py-4 sm:pt-0">

        {{-- @if (Route::has('login'))
        <div class="hidden fixed top-0 right-0 px-6 py-4 sm:block">
            @auth
            <a href="{{ url('/dashboard') }}" class="text-sm text-gray-700 underline">Home</a>
            @else
            <a href="{{ route('login') }}" class="text-sm text-gray-700 underline">Log in</a>
            
            @if (Route::has('register'))
            <a href="{{ route('register') }}" class="ml-4 text-sm text-gray-700 underline">Register</a>
            @endif
            @endauth
        </div>
        @endif --}}
        
        <div class="custom-container">
            <div class="flex justify-center pt-8 sm:pt-0">
                <x-application-logo/>
            </div>

            <div class="mt-4 text-xl text-gray-800 dark:text-gray-100">
                Feedback Management System
            </div>
            
            <div class="mt-8">
                @if (Route::has('login'))
                    <div class="flex flex-row justify-evenly">
                        @auth
                        <a href="{{ url('/dashboard') }}" class="text-sm text-gray-700 underline">Home</a>
                        @else

                        <x-button-link href="{{ route('login') }}">Log in</x-button-link>
                        
                        @if (Route::has('register'))
                            <x-button-link href="{{ route('register') }}">Register</x-button-link>
                        @endif
                        @endauth
                    </div>
                @endif
            </div>
            
        </div>
    </div>
</x-guest-layout>