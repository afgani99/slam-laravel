<x-app-layout>
    <x-slot name="header">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
            <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">notifications_active</span>
        </div>
        <div>
            <h2 class="text-[22px] font-semibold tracking-tight text-white">Tickets</h2>
            <p class="mt-1 text-sm text-neutral-500">Manage support tickets.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="flex flex-col gap-4 rounded-2xl border border-white/5 bg-[#161616] p-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('tickets.index', request()->except('status', 'page')) }}"
                   class="rounded-xl px-5 py-2.5 text-sm font-medium transition duration-200 {{ !$status ? 'bg-orange-500 text-white' : 'border border-white/5 bg-[#1b1b1b] text-neutral-400 hover:bg-white/5' }}">
                    All
                </a>
                @foreach (['open' => 'Opened', 'pending' => 'Pending', 'closed' => 'Closed'] as $value => $label)
                    <a href="{{ route('tickets.index', ['status' => $value] + request()->except('status', 'page')) }}"
                       class="rounded-xl px-5 py-2.5 text-sm font-medium transition duration-200 {{ $status === $value ? 'bg-orange-500 text-white' : 'border border-white/5 bg-[#1b1b1b] text-neutral-400 hover:bg-white/5' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            <a href="{{ route('tickets.create') }}" class="slam-primary-btn">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Buat Ticket
            </a>
        </div>

        <div class="slam-card p-6">
            <div class="overflow-hidden rounded-2xl border border-white/5 bg-[#151515]">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-white/5 text-[11px] uppercase tracking-[0.18em] text-neutral-500">
                        <tr>
                            <th class="px-6 py-4">Ticket</th>
                            <th class="px-6 py-4">CID</th>
                            <th class="px-6 py-4">Pelanggan</th>
                            <th class="px-6 py-4">Kasus</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Mulai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($tickets as $ticket)
                            <tr class="transition duration-200 hover:bg-white/5">
                                <td class="px-6 py-4 font-medium text-orange-400"><a href="{{ route('tickets.show', $ticket) }}">{{ $ticket->ticket_number }}</a></td>
                                <td class="px-6 py-4 text-neutral-300">{{ $ticket->cid->cid }}</td>
                                <td class="px-6 py-4 text-neutral-300">{{ $ticket->cid->customer_name }}</td>
                                <td class="px-6 py-4 text-neutral-300">{{ $ticket->case_type }}</td>
                                <td class="px-6 py-4">@include('tickets._status-badge', ['status' => $ticket->status])</td>
                                <td class="px-6 py-4 text-neutral-400">{{ $ticket->started_at?->format('d M H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center text-neutral-500">No tickets found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-6">{{ $tickets->links() }}</div>
        </div>
    </div>
</x-app-layout>
