<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                {{ __('Feedback No. '.$feedback->id) }}
            </h2>
        </div>
    </x-slot>

    <x-modal-container>
        <x-slot name="content">
            <div class="p-6 bg-white dark:bg-gray-800">
                <div class="mb-8">
                    <h2 class="font-semibold dark:text-gray-300">Feedback Description</h2>
                    <p class="text-sm dark:text-gray-300">{{$feedback->description}}</p>
                    <h2 class="font-semibold mt-4 dark:text-gray-300">Feedback Rating: <span>{{$feedback->rating}}</span></h2>
                    <h2 class="font-semibold mt-4 dark:text-gray-300">Counter: <span>
                        @if(empty($feedback->loginsession))
                            {{ $feedback->counter->number }} ({{ $feedback->counter->branch->name }})
                        @else
                            {{$feedback->loginsession->counter->id}}
                        @endif
                    </span></h2>
                    <h2 class="font-semibold mt-4 dark:text-gray-300">Cashier: <span>
                        @if(empty($feedback->loginsession))
                            N/A
                        @elseif(empty($feedback->loginsession->counter_user))
                            N/A
                        @else
                            {{$feedback->loginsession->counter_user->name}}
                        @endif
                    </span></h2>
                    <h2 class="font-semibold mt-4 dark:text-gray-300">Feedback Date Submission</h2>
                    <p class="text-sm dark:text-gray-300">{{ date('d-m-Y H:m', $feedback->feedback_time_submission) }}</p>
                </div>
                <div class="space-x-2">
                    <button @click="open = !open" type="button" class="bg-red-600 hover:bg-red-700 border-red-500 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline focus:ring focus:ring-red-500 dark:focus:ring-red-500">
                        Delete
                    </button>
                    <x-back-button>Go Back</x-back-button>
                </div>
            </div>
        </x-slot>

        <x-slot name="modalcontent">
            <div class="bg-white dark:bg-gray-600 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <!-- Heroicon name: outline/exclamation -->
                        <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg leading-6 font-medium dark:text-gray-200 text-gray-900" id="modal-title">
                                Delete Feedback
                            </h3>
                            <button class="focus:ring focus:ring-indigo-500 dark:focus:ring-gray-100 rounded-md dark:text-gray-100">
                                <svg @click="open = false" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 cursor-pointer" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <div class="mt-2">
                            <p class="text-base text-gray-500 dark:text-gray-200">
                                Are you sure you want to delete the feedback? This action cannot be undone.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </x-slot>

        <x-slot name="actionbutton">
            <form method="POST" action="{{ route('feedback.delete', $feedback) }}">
                @method("delete")
                @csrf
                <button type="submit" class="w-full inline-flex rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 dark:bg-red-500 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring focus:ring-red-500 dark:focus:ring-red-300 sm:ml-3 sm:w-auto sm:text-sm">
                    Delete
                </button>
            </form>
        </x-slot>

    </x-modal-container>
</x-app-layout>
