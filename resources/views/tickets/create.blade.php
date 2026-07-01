<x-app-layout>
    <x-slot name="header">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
            <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">add_task</span>
        </div>
        <div>
            <h2 class="text-[22px] font-semibold tracking-tight text-white">Buat Ticket</h2>
            <p class="mt-1 text-sm text-neutral-500">Input laporan gangguan untuk CID/link.</p>
        </div>
    </x-slot>

    <div class="max-w-4xl space-y-6">
        <div class="flex items-center gap-3 rounded-2xl border border-white/5 bg-[#161616] p-4 text-sm text-neutral-400">
            <span class="material-symbols-outlined text-[#e66a4a]">add_task</span>
            Form input laporan gangguan
        </div>
        <div class="rounded-xl border border-neutral-800 bg-neutral-900">
            <form method="POST" action="{{ route('tickets.store') }}" class="p-6">
                @include('tickets._form', [
                    'submitLabel' => 'Simpan Ticket',
                    'cancelUrl' => route('tickets.index'),
                    'showCompletionFields' => false,
                ])
            </form>
        </div>
    </div>
</x-app-layout>
