<x-app-layout>
    <x-slot name="header">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
            <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">edit</span>
        </div>
        <div>
            <h2 class="text-[22px] font-semibold tracking-tight text-white">Edit Ticket {{ $ticket->ticket_number }}</h2>
            <p class="mt-1 text-sm text-neutral-500">Perbarui data laporan gangguan.</p>
        </div>
    </x-slot>

    <div class="max-w-4xl">
        <div class="rounded-xl border border-neutral-800 bg-neutral-900">
            <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="p-6">
                @method('PUT')
                @include('tickets._form', [
                    'submitLabel' => 'Update Ticket',
                    'cancelUrl' => route('tickets.show', $ticket),
                    'showCompletionFields' => false,
                ])
            </form>
        </div>
    </div>
</x-app-layout>
