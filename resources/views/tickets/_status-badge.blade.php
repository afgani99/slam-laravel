@php
    $classes = match ($status) {
        'open' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
        'pending' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
        'closed' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
        'cancelled' => 'bg-red-500/10 text-red-400 border-red-500/30',
        default => 'bg-neutral-500/10 text-neutral-400 border-neutral-500/30',
    };
@endphp

<span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $classes }}">
    {{ $status === 'pending' ? 'Pending' : ucfirst($status) }}
</span>
