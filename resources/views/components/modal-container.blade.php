<div x-data="modal">
    <div class="mx-auto">
        <div class="overflow-hidden shadow-sm sm:rounded-lg divide-y divide-gray-400 dark:divide-gray-700">
            {{ $content }}
        </div>
    </div>

    {{-- Modal Start Here --}}
    <div class="fixed inset-0 overflow-y-auto pointer-events-none" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                style="display: none"
                class="fixed bg-black bg-opacity-50 min-h-screen inset-0 transition-opacity pointer-events-auto"
                ></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                style="display: none"
                class="inline-block align-bottom rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full pointer-events-auto">

                {{ $modalcontent }}

                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    {{ $actionbutton ?? '' }}
                    <button @click="closeModal()" type="button" class="mt-3 w-full inline-flex rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring focus:ring-indigo-500 dark:focus:ring-gray-200 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>
