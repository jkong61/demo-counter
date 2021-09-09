<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                {{ __('Counter User No. '.$counteruser->id) }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden shadow-sm sm:rounded-lg divide-y divide-gray-400 dark:divide-gray-700">
                <div class="p-6 bg-white dark:bg-gray-800">
                    <div class="mb-8">
                        <h2 class="font-semibold dark:text-gray-300">Counter User Name:</h2>
                        <p class="text-sm dark:text-gray-300">{{$counteruser->name}}</p>
                        <h2 class="font-semibold mt-4 dark:text-gray-300">Counter Display Name:</h2>
                        <p class="text-sm dark:text-gray-300">{{$counteruser->sname}}</p>
                        <h2 class="font-semibold mt-4 dark:text-gray-300">Position:</h2>
                        <p class="text-sm dark:text-gray-300">{{$counteruser->position ?? 'N/A'}}</p>
                    </div>

                    <div class="space-x-2">
                        <x-back-button>Go Back</x-back-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</x-app-layout>
