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

    <div x-data="{ showCreateGamasModal: false }" class="space-y-6">
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
                @if(in_array(auth()->user()->role, ['admin', 'operator']))
                    <button @click="showCreateGamasModal = true" class="slam-primary-btn shrink-0">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        Buat GAMAS
                    </button>
                @endif
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
                            @if(auth()->user()->role === 'admin')
                                <th class="px-6 py-4 text-right font-medium">Aksi</th>
                            @endif
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
                                @if(auth()->user()->role === 'admin')
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
                                @endif
                                
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

        {{-- Modal Buat GAMAS --}}
        <div x-show="showCreateGamasModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4"
            x-transition:enter="transition duration-200 ease-out"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition duration-150 ease-in"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-on:keydown.escape.window="showCreateGamasModal = false"
        >
            <div x-show="showCreateGamasModal" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/60 backdrop-blur-sm" x-on:click="showCreateGamasModal = false" aria-hidden="true"></div>

            <div x-show="showCreateGamasModal" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-6 opacity-0 scale-[0.97]" x-transition:enter-end="translate-y-0 opacity-100 scale-100" x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="translate-y-0 opacity-100 scale-100" x-transition:leave-end="translate-y-6 opacity-0 scale-[0.97]" class="relative z-10 w-full max-w-3xl">
                <div class="rounded-2xl border border-white/10 bg-[#1f1f1f] shadow-2xl shadow-black/50">
                    {{-- Header --}}
                    <div class="flex items-center gap-3 px-5 pb-2 pt-4">
                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="showCreateGamasModal = false" class="h-3 w-3 rounded-full bg-[#ff5f57] ring-1 ring-black/20 transition hover:opacity-90" aria-label="Close modal"></button>
                            <span class="h-3 w-3 rounded-full bg-[#febc2e] ring-1 ring-black/20 opacity-60"></span>
                            <span class="h-3 w-3 rounded-full bg-[#28c840] ring-1 ring-black/20 opacity-60"></span>
                        </div>
                        <h3 class="text-sm font-semibold text-white/80">Buat GAMAS</h3>
                    </div>

                    {{-- Form --}}
                    <form method="POST" action="{{ route('gamas.store') }}" class="p-6">
                        @csrf
                        <input type="hidden" name="_modal" value="1">

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <x-input-label for="modal_vendor_ticket_number" value="Ticket ID Vendor" />
                                <x-text-input id="modal_vendor_ticket_number" name="vendor_ticket_number" type="text" class="mt-1 block w-full" :value="old('vendor_ticket_number')" />
                                <x-input-error class="mt-2" :messages="$errors->get('vendor_ticket_number')" />
                            </div>
                            <div>
                                <x-input-label for="modal_case_type" value="Kasus" />
                                <div class="relative mt-1">
                                    <select id="modal_case_type" name="case_type" class="block h-[42px] w-full appearance-none rounded-lg border border-neutral-700 bg-neutral-900 pr-10 text-neutral-100 shadow-sm focus:border-orange-500 focus:ring-orange-500/30" required>
                                        <option value="" class="bg-neutral-900">Pilih kasus</option>
                                        @foreach ($caseTypes as $caseType)
                                            <option value="{{ $caseType }}" class="bg-neutral-900" @selected(old('case_type') === $caseType)>{{ $caseType }}</option>
                                        @endforeach
                                    </select>
                                    <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-neutral-500"><span class="material-symbols-outlined text-[18px]">expand_more</span></span>
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('case_type')" />
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label for="modal_started_at" value="Waktu Mulai" />
                                <x-text-input id="modal_started_at" name="started_at" type="datetime-local" class="mt-1 block w-full" :value="old('started_at')" required />
                                <x-input-error class="mt-2" :messages="$errors->get('started_at')" />
                            </div>
                        </div>

                        <div class="mt-5">
                            <x-input-label value="Pilih CID Terdampak" />
                            <p class="mt-1 text-xs text-neutral-500">Cari dan pilih CID yang terdampak gangguan ini.</p>
                            <div x-data='{
                                search: "",
                                selectedCids: @js(old("cid_ids", [])),
                                cids: {!! $cids->toJson() !!},
                                get filteredCids() {
                                    const s = this.search.toLowerCase();
                                    if (!s) return this.cids;
                                    return this.cids.filter(c =>
                                        c.cid.toLowerCase().includes(s) ||
                                        (c.customer_name && c.customer_name.toLowerCase().includes(s)) ||
                                        (c.cid_is && c.cid_is.toLowerCase().includes(s)) ||
                                        (c.vendor_name && c.vendor_name.toLowerCase().includes(s)) ||
                                        (c.service && c.service.toLowerCase().includes(s))
                                    );
                                },
                                toggleCid(id) {
                                    const idx = this.selectedCids.indexOf(id);
                                    if (idx === -1) {
                                        this.selectedCids.push(id);
                                    } else {
                                        this.selectedCids.splice(idx, 1);
                                    }
                                },
                                selectAll(filtered) {
                                    filtered.forEach(c => {
                                        if (!this.selectedCids.includes(c.id)) {
                                            this.selectedCids.push(c.id);
                                        }
                                    });
                                },
                                deselectAll() {
                                    this.selectedCids = [];
                                }
                            }' class="mt-3 space-y-3">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                    <x-text-input type="text" x-model="search" placeholder="Cari CID / Nama Pelanggan / CID IS / Vendor / Service..." class="flex-1" autocomplete="off" />
                                    <div class="flex gap-2">
                                        <button type="button" @click="selectAll(filteredCids)" class="rounded-lg border border-white/5 bg-[#262626] px-3 py-1.5 text-[11px] text-neutral-400 hover:bg-[#2f2f2f] hover:text-white">Select All</button>
                                        <button type="button" @click="deselectAll()" class="rounded-lg border border-white/5 bg-[#262626] px-3 py-1.5 text-[11px] text-neutral-400 hover:bg-[#2f2f2f] hover:text-white">Clear</button>
                                    </div>
                                </div>

                                <p class="text-xs text-neutral-500" x-text="selectedCids.length + ' CID terpilih'"></p>

                                <div class="max-h-[120px] overflow-auto rounded-lg border border-white/5">
                                    <template x-for="cid in filteredCids" :key="cid.id">
                                        <label class="flex cursor-pointer items-center gap-3 border-b border-white/5 px-4 py-3 transition hover:bg-white/[0.03]">
                                            <input type="checkbox" name="cid_ids[]" :value="cid.id"
                                                :checked="selectedCids.includes(cid.id)"
                                                @change="toggleCid(cid.id)"
                                                class="h-4 w-4 rounded border-neutral-600 bg-neutral-800 text-orange-500 focus:ring-orange-500/30">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-white" x-text="cid.cid"></p>
                                                <p class="text-xs text-neutral-500" x-text="(cid.customer_name || '-') + (cid.service ? ' — ' + cid.service : '')"></p>
                                            </div>
                                            <span class="text-[11px] text-neutral-500 shrink-0" x-text="cid.vendor_name || '-'"></span>
                                        </label>
                                    </template>
                                    <p x-show="filteredCids.length === 0" class="px-4 py-8 text-center text-sm text-neutral-500">Tidak ditemukan CID.</p>
                                </div>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('cid_ids')" />
                        </div>

                        <div class="mt-6 flex items-center gap-3 border-t border-white/5 pt-5">
                            <button type="submit" class="inline-flex h-[42px] items-center gap-2 rounded-xl bg-[#e66a4a] px-6 text-sm font-medium text-white transition hover:bg-[#ff7b5c]">
                                <span class="material-symbols-outlined text-[18px]">bolt</span>
                                Buat GAMAS
                            </button>
                            <button type="button" @click="showCreateGamasModal = false" class="inline-flex h-[42px] items-center gap-2 rounded-xl border border-white/10 bg-[#262626] px-6 text-sm text-neutral-300 transition hover:bg-[#2f2f2f] hover:text-white">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
