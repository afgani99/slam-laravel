<x-app-layout>
    <x-slot name="header">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
            <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">add_link</span>
        </div>
        <div>
            <h2 class="text-[22px] font-semibold tracking-tight text-white">Tambah CID</h2>
            <p class="mt-1 text-sm text-neutral-500">Tambahkan master data link/customer baru.</p>
        </div>
    </x-slot>

    <div class="max-w-4xl">
        <div class="rounded-xl border border-neutral-800 bg-neutral-900">
            <form method="POST" action="{{ route('cids.store') }}" class="p-6">
                @include('cids._form', [
                    'submitLabel' => 'Simpan CID',
                    'cancelUrl' => route('cids.index'),
                ])
            </form>
        </div>
    </div>
</x-app-layout>
