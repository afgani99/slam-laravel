@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-xl bg-orange-500/15 px-4 py-3 text-start text-base font-medium text-white ring-1 ring-orange-500/20 transition duration-200'
            : 'block w-full rounded-xl px-4 py-3 text-start text-base font-medium text-neutral-400 transition duration-200 hover:bg-white/5 hover:text-white';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
