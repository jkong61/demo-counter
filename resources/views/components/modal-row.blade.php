@props(['title','html'])

<div class="mt-2">
    <p class="text-sm text-gray-500 dark:text-gray-400">
        {{ $title }}: 
    </p>
    <span class="text-gray-500 dark:text-gray-200 font-bold" x-html="{{ $html }}"></span>
</div>