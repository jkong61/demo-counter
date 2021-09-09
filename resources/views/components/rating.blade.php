@props(['rating'])

@php
    $default_class = "px-2 inline-flex text-xs leading-5 font-semibold rounded-full";
    switch ($rating) {
        case 1:
        case 2:
            $classes = $default_class . " bg-red-100 text-red-800";
            break;
        case 4:
        case 5:
            $classes = $default_class . " bg-green-100 text-green-800";
            break;
        default:
            $classes = $default_class ." bg-yellow-100 text-yellow-800";
            break;
    }
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{$rating}}
</span>