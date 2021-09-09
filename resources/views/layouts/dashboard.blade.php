<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                {{ $title }}
            </h2>
            {{ $addbutton ?? '' }}
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-900 overflow-hidden sm:rounded-lg space-y-4 bg-opacity-0">
                {{ $slot }}
            </div>
        </div>
    </div>
</x-app-layout>
