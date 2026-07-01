<x-app-layout>
    <x-slot name="header">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
            <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">confirmation_number</span>
        </div>
        <div>
            <h2 class="text-[22px] font-semibold tracking-tight text-white">Ticket {{ $ticket->ticket_number }}</h2>
            <p class="mt-1 text-sm text-neutral-500">Detail laporan gangguan.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <div class="flex items-center gap-3 rounded-lg border border-emerald-800 bg-emerald-950 p-4 text-sm text-emerald-400">
                <span class="material-symbols-outlined text-emerald-500">check_circle</span>
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="flex items-center gap-3 rounded-lg border border-red-800 bg-red-950 p-4 text-sm text-red-400">
                <span class="material-symbols-outlined text-red-500">error</span>
                {{ session('error') }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Main Info -->
            <div class="rounded-xl border border-neutral-800 bg-neutral-900 lg:col-span-2">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-white">Informasi Ticket</h3>
                        @include('tickets._status-badge', ['status' => $ticket->status])
                    </div>

                    <dl class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div><dt class="text-xs font-medium uppercase tracking-wider text-neutral-500">Ticket ID</dt><dd class="mt-1 text-sm font-semibold text-white">{{ $ticket->ticket_number }}</dd></div>
                        <div><dt class="text-xs font-medium uppercase tracking-wider text-neutral-500">Ticket ID Vendor</dt><dd class="mt-1 text-sm text-neutral-300">{{ $ticket->vendor_ticket_number ?: '-' }}</dd></div>
                        <div><dt class="text-xs font-medium uppercase tracking-wider text-neutral-500">CID</dt><dd class="mt-1 text-sm text-neutral-300">{{ $ticket->cid->cid }}</dd></div>
                        <div><dt class="text-xs font-medium uppercase tracking-wider text-neutral-500">Vendor</dt><dd class="mt-1 text-sm text-neutral-300">{{ $ticket->cid->vendor_name }}</dd></div>
                        <div><dt class="text-xs font-medium uppercase tracking-wider text-neutral-500">Pelanggan</dt><dd class="mt-1 text-sm text-neutral-300">{{ $ticket->cid->customer_name }}</dd></div>
                        <div><dt class="text-xs font-medium uppercase tracking-wider text-neutral-500">Service</dt><dd class="mt-1 text-sm text-neutral-300">{{ $ticket->cid->service }}</dd></div>
                        <div><dt class="text-xs font-medium uppercase tracking-wider text-neutral-500">Kasus</dt><dd class="mt-1 text-sm text-neutral-300">{{ $ticket->case_type }}</dd></div>
                        <div><dt class="text-xs font-medium uppercase tracking-wider text-neutral-500">Mulai</dt><dd class="mt-1 text-sm text-neutral-400">{{ $ticket->started_at?->format('d M Y H:i') }}</dd></div>
                        <div><dt class="text-xs font-medium uppercase tracking-wider text-neutral-500">Selesai</dt><dd class="mt-1 text-sm text-neutral-400">{{ $ticket->finished_at?->format('d M Y H:i') ?: '-' }}</dd></div>
                        <div><dt class="text-xs font-medium uppercase tracking-wider text-neutral-500">Closed At</dt><dd class="mt-1 text-sm text-neutral-400">{{ $ticket->closed_at?->format('d M Y H:i') ?: '-' }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-xs font-medium uppercase tracking-wider text-neutral-500">RFO / Action</dt><dd class="mt-1 whitespace-pre-line text-sm text-neutral-300">{{ $ticket->rfo_action ?: '-' }}</dd></div>
                    </dl>
                </div>
            </div>

            <!-- Side Actions -->
            <div class="space-y-4">
                @if ($ticket->isOpen())
                    <div class="rounded-xl border border-neutral-800 bg-neutral-900">
                        <form method="POST" action="{{ route('tickets.pending', $ticket) }}" class="p-6">
                            @csrf
                            <h3 class="text-sm font-semibold text-white">Pending Ticket</h3>
                            <div class="mt-4">
                                <x-input-label for="pending_started_at" value="Waktu Mulai Pending" />
                                <x-text-input id="pending_started_at" name="started_at" type="datetime-local" class="mt-1 block w-full" required />
                                <x-input-error class="mt-2" :messages="$errors->get('started_at')" />
                            </div>
                            <div class="mt-4">
                                <x-input-label for="pending_note" value="Catatan" />
                                <textarea id="pending_note" name="note" rows="3" class="mt-1 block w-full rounded-lg border-neutral-700 bg-neutral-900 text-neutral-100 placeholder-neutral-500 focus:border-orange-500 focus:ring-orange-500/30"></textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('note')" />
                            </div>
                            <x-primary-button class="mt-4">Set Pending</x-primary-button>
                        </form>
                    </div>

                    <div class="rounded-xl border border-neutral-800 bg-neutral-900">
                        <form method="POST" action="{{ route('tickets.close', $ticket) }}" class="p-6">
                            @csrf
                            <h3 class="text-sm font-semibold text-white">Tutup Ticket</h3>
                            <div class="mt-4">
                                <x-input-label for="finished_at" value="Waktu Selesai" />
                                <x-text-input id="finished_at" name="finished_at" type="datetime-local" class="mt-1 block w-full" :value="old('finished_at', $ticket->finished_at?->format('Y-m-d\TH:i'))" required />
                                <x-input-error class="mt-2" :messages="$errors->get('finished_at')" />
                            </div>
                            <div class="mt-4">
                                <x-input-label for="rfo_action" value="RFO / Action" />
                                <textarea id="rfo_action" name="rfo_action" rows="4" class="mt-1 block w-full rounded-lg border-neutral-700 bg-neutral-900 text-neutral-100 placeholder-neutral-500 focus:border-orange-500 focus:ring-orange-500/30" required>{{ old('rfo_action', $ticket->rfo_action) }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('rfo_action')" />
                            </div>
                            <x-primary-button class="mt-4">Tutup Ticket</x-primary-button>
                        </form>
                    </div>
                @endif

                @if ($ticket->isPending())
                    <div class="rounded-xl border border-neutral-800 bg-neutral-900">
                        <form method="POST" action="{{ route('tickets.resume', $ticket) }}" class="p-6">
                            @csrf
                            <h3 class="text-sm font-semibold text-white">Lanjutkan Ticket</h3>
                            <div class="mt-4">
                                <x-input-label for="pending_ended_at" value="Waktu Lanjut" />
                                <x-text-input id="pending_ended_at" name="ended_at" type="datetime-local" class="mt-1 block w-full" required />
                                <x-input-error class="mt-2" :messages="$errors->get('ended_at')" />
                            </div>
                            <x-primary-button class="mt-4">Lanjutkan</x-primary-button>
                        </form>
                    </div>
                @endif

                @if (! $ticket->isClosed())
                    <div class="rounded-xl border border-red-900/50 bg-red-950/30">
                        <form method="POST" action="{{ route('tickets.destroy', $ticket) }}" class="p-6" onsubmit="return confirm('Hapus ticket ini?')">
                            @csrf
                            @method('DELETE')
                            <h3 class="text-sm font-semibold text-red-400">Hapus Ticket</h3>
                            <p class="mt-2 text-sm text-neutral-500">Ticket yang belum closed dapat dihapus jika salah input.</p>
                            <button type="submit" class="mt-4 inline-flex items-center gap-2 rounded-lg border border-red-800 bg-red-600/10 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-red-400 transition-colors hover:bg-red-600/20">
                                <span class="material-symbols-outlined text-sm">delete</span>
                                Hapus Ticket
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <!-- Pending History -->
        <div class="rounded-xl border border-neutral-800 bg-neutral-900">
            <div class="p-6">
                <h3 class="text-sm font-semibold text-white">Riwayat Pending</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-neutral-800">
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">Mulai</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">Selesai</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-800">
                            @forelse ($ticket->pendingIntervals as $interval)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-sm text-neutral-300">{{ $interval->started_at?->format('d M Y H:i') }}</td>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-sm text-neutral-400">{{ $interval->ended_at?->format('d M Y H:i') ?: 'Masih pending' }}</td>
                                    <td class="px-4 py-3.5 text-sm text-neutral-400">{{ $interval->note ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-4 py-12 text-center text-sm text-neutral-500">Belum ada riwayat pending.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
