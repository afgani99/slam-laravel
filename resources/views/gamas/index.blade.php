<x-app-layout>
    <x-slot name="header">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
            <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">sensors</span>
        </div>
        <div>
            <h2 class="text-[22px] font-semibold tracking-tight text-white">GAMAS</h2>
            <p class="mt-1 text-sm text-neutral-500">Gangguan Massal — manage bulk incidents.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Search & Filter Card -->
        <div class="slam-panel p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
                        <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">filter_alt</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-white">Filter & Pencarian</p>
                        <p class="text-xs text-neutral-500">Cari berdasarkan nomor GAMAS, vendor, case type, CID, atau pelanggan</p>
                    </div>
                </div>
                <a href="{{ route('gamas.create') }}" class="slam-primary-btn shrink-0">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Buat GAMAS
                </a>
            </div>

            <form method="GET" action="{{ route('gamas.index') }}" class="mt-5 grid gap-3 lg:grid-cols-[1fr_110px_155px] lg:items-end">
                {{-- Search Input --}}
                <div class="flex flex-col gap-1.5">
                    <x-input-label for="search" value="Pencarian" class="text-xs font-medium uppercase tracking-[0.15em] text-neutral-400" />
                    <div class="relative">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-neutral-500">search</span>
                        <x-text-input id="search" name="search" type="search" class="block w-full pl-10" placeholder="Nomor GAMAS, vendor, case type…" :value="$search" />
                    </div>
                </div>

                {{-- Per Page --}}
                <div class="flex flex-col gap-1.5">
                    <x-input-label for="per_page" value="Tampilkan" class="text-xs font-medium uppercase tracking-[0.15em] text-neutral-400" />
                    <select id="per_page" name="per_page"
                        class="block w-full rounded-xl border border-white/10 bg-[#262626] px-4 py-2.5 text-sm text-white transition focus:border-[#e66a4a] focus:outline-none focus:ring-2 focus:ring-[#e66a4a]/20">
                        @foreach ([10, 25, 50] as $option)
                            <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} baris</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="flex flex-col gap-1.5">
                    <x-input-label for="status" value="Status" class="text-xs font-medium uppercase tracking-[0.15em] text-neutral-400" />
                    <select id="status" name="status"
                        class="block w-full rounded-xl border border-white/10 bg-[#262626] px-4 py-2.5 text-sm text-white transition focus:border-[#e66a4a] focus:outline-none focus:ring-2 focus:ring-[#e66a4a]/20">
                        <option value="">Semua Status</option>
                        @foreach (\App\Models\Gamas::STATUSES as $statusOption)
                            <option value="{{ $statusOption }}" {{ $status == $statusOption ? 'selected' : '' }}>
                                {{ ucfirst($statusOption) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2 border-t border-white/5 pt-4 lg:col-span-3">
                    <button type="submit"
                        class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-[#e66a4a] px-4 text-sm font-medium text-white shadow-sm shadow-[#e66a4a]/20 transition hover:bg-[#ff7b5c] active:scale-[0.97]">
                        <span class="material-symbols-outlined text-[16px]">tune</span>
                        Terapkan Filter
                    </button>
                    @if (request()->has('search'))
                        <a href="{{ route('gamas.index') }}"
                            class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-white/10 bg-transparent px-4 text-sm text-neutral-400 transition hover:border-white/20 hover:text-white active:scale-[0.97]">
                            <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                            Reset
                        </a>
                    @endif
                    <span class="ml-auto text-xs text-neutral-500">
                        {{ $gamasList->total() }} data ditemukan
                    </span>
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <div class="slam-panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-white/5 bg-[#2a2a2a] text-[11px] uppercase tracking-[0.18em] text-neutral-500">
                        <tr>
                            <th class="px-6 py-4 font-medium">Gamas #</th>
                            <th class="px-6 py-4 font-medium">Vendor</th>
                            <th class="px-6 py-4 font-medium">Case Type</th>
                            <th class="px-6 py-4 font-medium">Mulai</th>
                            <th class="px-6 py-4 font-medium">Status</th>
                            <th class="px-6 py-4 text-center font-medium">Tiket</th>
                            <th class="px-6 py-4 font-medium">Selesai</th>
                            <th class="px-6 py-4 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($gamasList as $gamas)
                            <tr class="transition duration-150 hover:bg-white/[0.03]">
                                <td class="px-6 py-4">
                                    <a href="{{ route('gamas.show', $gamas) }}" class="font-medium text-[#e66a4a] transition hover:text-[#ff7b5c]">
                                        {{ $gamas->gamas_number }}
                                    </a>
                                    @if ($gamas->vendor_ticket_number)
                                        <p class="mt-1 text-xs text-neutral-500">{{ $gamas->vendor_ticket_number }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-neutral-200">
                                    {{ $gamas->tickets->first()?->cid->vendor_name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-neutral-300">{{ $gamas->case_type }}</td>
                                <td class="px-6 py-4 text-neutral-400">{{ $gamas->started_at?->format('d M H:i') }}</td>
                                <td class="px-6 py-4">@include('tickets._status-badge', ['status' => $gamas->status])</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center h-7 min-w-[28px] rounded-lg bg-[#262626] px-2 text-sm tabular-nums text-neutral-300">
                                        {{ $gamas->tickets_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-neutral-400">{{ $gamas->finished_at?->format('d M H:i') ?: '-' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('gamas.destroy', $gamas) }}" method="POST"
                                          onsubmit="return confirm('Hapus GAMAS ini dan semua tiket terkait?')"
                                          class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-white/5 bg-[#262626] text-neutral-300 transition hover:border-red-900/50 hover:bg-red-900/20 hover:text-red-400"
                                                title="Hapus GAMAS" aria-label="Hapus GAMAS">
                                            <span class="material-symbols-outlined text-[14px]">delete</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-16 text-center">
                                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-white/5">
                                        <span class="material-symbols-outlined text-2xl text-neutral-500">database_off</span>
                                    </div>
                                    <p class="text-sm text-neutral-500">Belum ada data GAMAS.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="border-t border-white/5 pt-4">
            {{ $gamasList->links() }}
        </div>
    </div>
</x-app-layout>
