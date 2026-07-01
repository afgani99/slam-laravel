@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-xl border border-white/10 bg-[#151515] px-4 py-3 text-sm text-neutral-100 placeholder:text-neutral-500 shadow-none outline-none transition duration-200 focus:border-orange-500/60 focus:ring-2 focus:ring-orange-500/20']) }}>
