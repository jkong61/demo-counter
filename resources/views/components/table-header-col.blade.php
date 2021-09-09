@props(['class'])

@php
    $classes = $class ?? "px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-50 uppercase tracking-wider";
@endphp

<th {{ $attributes->merge(['scope' => 'col' ,'class' => $classes]) }}>
    {{ $slot }}
</th>