<div class="flex flex-col space-y-2">
    <div class="overflow-x-auto sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600 shadow-sm">
            <thead class="bg-gray-50 dark:bg-gray-600">
                {{ $thead }}
            </thead>
            <tbody class="bg-white dark:bg-gray-400 divide-y divide-gray-200 dark:divide-gray-500">
                {{ $tbody }}
            </tbody>
        </table>
    </div>

    <div class="py-2 px-4">
        {{ $pagination }}
    </div>
</div> 