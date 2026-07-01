@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center rounded-xl px-3 py-2 text-sm font-medium text-white bg-orange-500/15 ring-1 ring-orange-500/20 transition duration-200'
            : 'inline-flex items-center rounded-xl px-3 py-2 text-sm font-medium text-neutral-400 transition duration-200 hover:bg-white/5 hover:text-white';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
