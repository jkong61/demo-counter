@props(['action'])

<div class="bg-gray-50 dark:bg-gray-800 sm:rounded-lg p-4 mb-4 shadow-sm">
    <form action={{ $action }} method="get">
        @csrf
        {{ $slot }}

        <x-button>Apply Filter</x-button>
        <a href="{{ $action }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
            Reset
        </a>
    </form>
</div>