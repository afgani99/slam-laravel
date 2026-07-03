<x-app-layout>
    <x-slot name="header">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
            <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">confirmation_number</span>
        </div>
        <div>
            <h2 class="text-[22px] font-semibold tracking-tight text-white">Ticket {{ $ticket->ticket_number }}</h2>
            <p class="mt-1 text-sm text-neutral-500">Detail ticket pelanggan</p>
        </div>
    </x-slot>

    <div x-data="{ showTicketModal: false, modalAction: 'edit' }" class="space-y-4">
        <div class="grid gap-4 lg:grid-cols-4">
            {{-- Main Info --}}
            <div class="slam-card lg:col-span-4">
                <div class="flex items-center justify-between border-b border-white/5 px-5 py-3">
                    <div class="flex items-center gap-2">
                        <h3 class="font-semibold text-white">Informasi Ticket</h3>
                        @include('tickets._status-badge', ['status' => $ticket->status])
                    </div>
                    <div class="flex items-center gap-2">
                        @if (! $ticket->isClosed())
                            {{-- Edit Button --}}
                            <button type="button" @click="modalAction = 'edit'; showTicketModal = true" class="inline-flex items-center gap-1.5 rounded-lg border border-white/5 bg-white/5 px-3 py-1.5 text-xs text-white transition hover:bg-white/10">
                                <span class="material-symbols-outlined text-[15px]">edit</span>
                                Edit
                            </button>

                            {{-- Pending / Resume Button --}}
                            @if (!$ticket->isPending())
                                <button type="button" @click="modalAction = 'pending'; showTicketModal = true" class="inline-flex items-center gap-1.5 rounded-lg border border-orange-900/30 bg-orange-900/10 px-3 py-1.5 text-xs text-orange-400 transition hover:bg-orange-900/20">
                                    <span class="material-symbols-outlined text-[15px]">pause</span>
                                    Set Pending
                                </button>
                            @else
                                <button type="button" @click="modalAction = 'resume'; showTicketModal = true" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-900/30 bg-emerald-900/10 px-3 py-1.5 text-xs text-emerald-400 transition hover:bg-emerald-900/20">
                                    <span class="material-symbols-outlined text-[15px]">play_arrow</span>
                                    Lanjutkan
                                </button>
                            @endif

                            {{-- Delete Button --}}
                            <form method="POST" action="{{ route('tickets.destroy', $ticket) }}" onsubmit="return confirm('Hapus ticket ini?')" class="inline-block">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-red-900/30 bg-red-900/10 px-3 py-1.5 text-xs text-red-400 transition hover:bg-red-900/20">
                                    <span class="material-symbols-outlined text-[15px]">delete</span>
                                    Hapus
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3 border-b border-white/5 p-5 sm:grid-cols-3">
                    <div class="rounded-xl border border-white/5 bg-[#1f1f1f] p-3">
                        <p class="text-[10px] uppercase tracking-wide text-neutral-500">Durasi Kendala</p>
                        <p class="mt-1 text-sm font-semibold text-white">{{ $durasiKendalaFormatted }}</p>
                    </div>
                    <div class="rounded-xl border border-white/5 bg-[#1f1f1f] p-3">
                        <p class="text-[10px] uppercase tracking-wide text-neutral-500">Durasi Efektif</p>
                        <p class="mt-1 text-sm font-semibold text-emerald-400">{{ $durasiEfektifFormatted }}</p>
                    </div>
                    <div class="rounded-xl border border-white/5 bg-[#1f1f1f] p-3">
                        <p class="text-[10px] uppercase tracking-wide text-neutral-500">Total Durasi Pending</p>
                        <p class="mt-1 text-sm font-semibold text-orange-400">{{ $totalPendingFormatted }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-x-6 gap-y-3 p-5 text-sm sm:grid-cols-3">
                    <div>
                        <p class="text-[10px] uppercase text-neutral-500">Ticket ID Vendor</p>
                        <p class="text-neutral-300">{{ $ticket->vendor_ticket_number ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase text-neutral-500">CID</p>
                        <p class="text-neutral-300">{{ $ticket->cid->cid }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase text-neutral-500">Pelanggan</p>
                        <p class="text-neutral-300">{{ $ticket->cid->customer_name }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase text-neutral-500">Service</p>
                        <p class="text-neutral-300">{{ $ticket->cid->service }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase text-neutral-500">Kasus</p>
                        <p class="text-neutral-300">{{ $ticket->case_type }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase text-neutral-500">Mulai</p>
                        <p class="text-neutral-400">{{ $ticket->started_at?->format('d M H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase text-neutral-500">Selesai</p>
                        <p class="text-neutral-400">{{ $ticket->finished_at?->format('d M H:i') ?: '-' }}</p>
                    </div>
                    <div class="col-span-2 sm:col-span-3">
                        <p class="text-[10px] uppercase text-neutral-500">RFO / Action</p>
                        <p class="mt-0.5 text-neutral-300">{{ $ticket->rfo_action ?: '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- History --}}
        <div class="slam-card p-5">
            <h3 class="mb-3 text-sm font-semibold text-white">Riwayat Pending</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-neutral-400">
                    <thead class="border-b border-white/5 uppercase text-neutral-600">
                        <tr>
                            <th class="px-2 py-2">Mulai</th>
                            <th class="px-2 py-2">Selesai</th>
                            <th class="px-2 py-2">Catatan</th>
                            <th class="px-2 py-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($ticket->pendingIntervals as $i)
                            <tr>
                                <td class="px-2 py-2">{{ $i->started_at?->format('d M H:i') }}</td>
                                <td class="px-2 py-2">{{ $i->ended_at?->format('d M H:i') ?: '-' }}</td>
                                <td class="px-2 py-2">{{ $i->note ?: '-' }}</td>
                                <td class="px-2 py-2">
                                    <div class="flex items-center gap-2">
                                        <form action="{{ route('tickets.pending-intervals.destroy', $i) }}" method="POST" onsubmit="return confirm('Hapus interval pending ini?')" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-white/5 bg-[#262626] text-neutral-300 transition hover:border-red-900/50 hover:bg-red-900/20 hover:text-red-400"
                                                title="Hapus Pending">
                                                <span class="material-symbols-outlined text-[14px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-2 py-4 text-center">No history</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- macOS Style Modals -->
        <div x-show="showTicketModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity x-on:keydown.escape.window="showTicketModal = false">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" x-on:click="showTicketModal = false"></div>
            <div class="relative w-full max-w-2xl rounded-2xl border border-white/10 bg-[#1f1f1f] shadow-2xl shadow-black/50" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
                {{-- macOS Header --}}
                <div class="flex items-center gap-3 px-5 pb-2 pt-4">
                    <div class="flex items-center gap-1.5">
                        <button type="button" @click="showTicketModal = false" class="h-3 w-3 rounded-full bg-[#ff5f57] ring-1 ring-black/20 transition hover:opacity-90" aria-label="Close modal"></button>
                        <span class="h-3 w-3 rounded-full bg-[#febc2e] ring-1 ring-black/20 opacity-60"></span>
                        <span class="h-3 w-3 rounded-full bg-[#28c840] ring-1 ring-black/20 opacity-60"></span>
                    </div>
                    <h3 class="text-sm font-semibold text-white/80" x-text="modalAction === 'edit' ? 'Edit Ticket' : (modalAction === 'pending' ? 'Set Pending Ticket' : (modalAction === 'resume' ? 'Lanjutkan Ticket' : 'Tutup Ticket'))"></h3>
                </div>

                {{-- Form Edit --}}
                <form x-show="modalAction === 'edit'" method="POST" action="{{ route('tickets.update', $ticket) }}" class="p-6">
                    @csrf @method('PUT')
                    <input type="hidden" name="cid_id" value="{{ $ticket->cid_id }}">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-1">
                            <label class="block text-[10px] uppercase text-neutral-500 mb-1">Ticket ID Vendor</label>
                            <input id="edit_vendor_ticket_number" name="vendor_ticket_number" class="w-full rounded-lg border border-neutral-700 bg-neutral-900 px-3 py-2 text-xs text-white" value="{{ $ticket->vendor_ticket_number }}">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-[10px] uppercase text-neutral-500 mb-1">Kasus</label>
                            <select id="edit_case_type" name="case_type" class="w-full rounded-lg border border-neutral-700 bg-neutral-900 px-3 py-2 text-xs text-white">
                                @foreach (\App\Models\Ticket::CASE_TYPES as $ct)<option value="{{ $ct }}" @selected($ticket->case_type===$ct)>{{ $ct }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-span-1">
                            <label class="block text-[10px] uppercase text-neutral-500 mb-1">Waktu Mulai</label>
                            <input id="edit_started_at" name="started_at" type="datetime-local" class="w-full rounded-lg border border-neutral-700 bg-neutral-900 px-3 py-2 text-xs text-white" value="{{ $ticket->started_at?->format('Y-m-d\TH:i') }}">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-[10px] uppercase text-neutral-500 mb-1">Waktu Selesai</label>
                            <input id="edit_finished_at" name="finished_at" type="datetime-local" class="w-full rounded-lg border border-neutral-700 bg-neutral-900 px-3 py-2 text-xs text-white" value="{{ $ticket->finished_at?->format('Y-m-d\TH:i') }}">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[10px] uppercase text-neutral-500 mb-1">RFO / Action</label>
                            <textarea id="edit_rfo_action" name="rfo_action" rows="3" class="w-full rounded-lg border border-neutral-700 bg-neutral-900 px-3 py-2 text-xs text-white">{{ $ticket->rfo_action }}</textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex items-center justify-end gap-3 border-t border-white/5 pt-4">
                        <button type="submit" class="rounded-lg bg-orange-600 px-4 py-2 text-xs font-semibold text-white">Simpan</button>
                        <button type="button" @click="
                            const finished = document.getElementById('edit_finished_at').value;
                            const rfo = document.getElementById('edit_rfo_action').value;
                            if(!finished || !rfo) { alert('Selesaikan Waktu Selesai dan RFO Action'); return; }
                            document.getElementById('close_finished_at').value = finished;
                            document.getElementById('close_rfo_action').value = rfo;
                            document.getElementById('closeForm').submit();
                        " class="rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white">Close Ticket</button>
                    </div>
                </form>

                {{-- Form Close --}}
                <form id="closeForm" x-show="modalAction === 'close'" method="POST" action="{{ route('tickets.close', $ticket) }}" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[10px] uppercase text-neutral-500 mb-1">Waktu Selesai</label>
                        <input id="close_finished_at" name="finished_at" type="datetime-local" class="w-full rounded-lg border border-neutral-700 bg-neutral-900 px-3 py-2 text-xs text-white" required>
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase text-neutral-500 mb-1">RFO / Action</label>
                        <textarea id="close_rfo_action" name="rfo_action" rows="3" class="w-full rounded-lg border border-neutral-700 bg-neutral-900 px-3 py-2 text-xs text-white" required></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white">Tutup Ticket</button>
                    </div>
                </form>

                {{-- Form Pending --}}
                <form x-show="modalAction === 'pending'" method="POST" action="{{ route('tickets.pending', $ticket) }}" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[10px] uppercase text-neutral-500 mb-1">Waktu Mulai Pending</label>
                        <input name="started_at" type="datetime-local" class="w-full rounded-lg border border-neutral-700 bg-neutral-900 px-3 py-2 text-xs text-white" required>
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase text-neutral-500 mb-1">Catatan</label>
                        <textarea name="note" rows="3" class="w-full rounded-lg border border-neutral-700 bg-neutral-900 px-3 py-2 text-xs text-white"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="submit" class="rounded-lg bg-orange-600 px-4 py-2 text-xs font-semibold text-white">Set Pending</button>
                    </div>
                </form>

                {{-- Form Resume --}}
                <form x-show="modalAction === 'resume'" method="POST" action="{{ route('tickets.resume', $ticket) }}" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[10px] uppercase text-neutral-500 mb-1">Waktu Dilanjutkan</label>
                        <input name="ended_at" type="datetime-local" class="w-full rounded-lg border border-neutral-700 bg-neutral-900 px-3 py-2 text-xs text-white" required>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white">Lanjutkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
