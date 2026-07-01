<div
    x-data="{
        show: @entangle($attributes->wire('model')).live ?? false,
        init() {
            this.$watch('show', value => {
                if (value) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            });
        },
    }"
    x-show="show"
    x-init="init()"
    x-on:keydown.escape.window="show = false"
    x-transition:enter="transition duration-200 ease-out"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition duration-150 ease-in"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto"
    style="display: none;"
>
    {{-- Backdrop --}}
    <div
        x-show="show"
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition duration-150 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm"
        x-on:click="show = false"
        aria-hidden="true"
    ></div>

    {{-- Panel --}}
    <div
        x-show="show"
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="translate-y-6 opacity-0 scale-[0.97]"
        x-transition:enter-end="translate-y-0 opacity-100 scale-100"
        x-transition:leave="transition duration-150 ease-in"
        x-transition:leave-start="translate-y-0 opacity-100 scale-100"
        x-transition:leave-end="translate-y-6 opacity-0 scale-[0.97]"
        class="relative z-10 w-full max-w-2xl"
        x-on:click.stop
    >
        <div class="mx-4 rounded-2xl border border-white/10 bg-[#1f1f1f] shadow-2xl shadow-black/50">
            {{ $slot }}
        </div>
    </div>
</div>
