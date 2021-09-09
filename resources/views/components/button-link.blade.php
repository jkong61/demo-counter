<a
    {{$attributes->merge(
        ['class' => "inline-block items-center px-4 py-2 rounded-lg transition bg-gray-800 hover:bg-gray-600 hover:-translate-y-0.5 focus:ring-bg-gray-800 focus:ring-opacity-50 focus:outline-none focus:ring active:bg-gray-900 uppercase tracking-wider font-semibold text-xs text-white shadow-lg sm:text-base"])
    }}>
    {{ $slot }}
</a>