<x-app-layout>
    <x-slot name="header">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
            <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">lan</span>
        </div>
        <div>
            <h2 class="text-[22px] font-semibold tracking-tight text-white">Master CID</h2>
            <p class="mt-1 text-sm text-neutral-500">Daftar link/customer untuk monitoring SLA.</p>
        </div>
    </x-slot>

    <div class="space-y-6" x-data="{ showCidModal: false, showEditCidModal: false, showTicketModal: false, editCid: {}, ticketCidId: null, ticketCidCid: '', ticketCidIs: '', ticketCidVendor: '', ticketCidCustomer: '', ticketCidService: '' }">
        <div class="slam-panel p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
                        <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">filter_alt</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-white">Filter & Pencarian</p>
                        <p class="text-xs text-neutral-500">Cari berdasarkan CID, vendor, pelanggan, atau service</p>
                    </div>
                </div>
                <button @click="showCidModal = true" class="slam-primary-btn shrink-0">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Tambah CID
                </button>
            </div>


            <form method="GET" action="{{ route('cids.index') }}" class="mt-5 grid gap-3 lg:grid-cols-[1fr_110px] lg:items-end">
                {{-- Search Input --}}
                <div class="flex flex-col gap-1.5">
                    <x-input-label for="search" value="Pencarian" class="text-xs font-medium uppercase tracking-[0.15em] text-neutral-400" />
                    <div class="relative">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-neutral-500">search</span>
                        <x-text-input id="search" name="search" type="search" class="block w-full pl-10" placeholder="CID, vendor, pelanggan, atau service…" :value="$search" />
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

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2 border-t border-white/5 pt-4 lg:col-span-2">
                    <button type="submit"
                        class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-[#e66a4a] px-4 text-sm font-medium text-white shadow-sm shadow-[#e66a4a]/20 transition hover:bg-[#ff7b5c] active:scale-[0.97]">
                        <span class="material-symbols-outlined text-[16px]">tune</span>
                        Terapkan Filter
                    </button>
                    @if (request()->has('search'))
                        <a href="{{ route('cids.index') }}"
                            class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-white/10 bg-transparent px-4 text-sm text-neutral-400 transition hover:border-white/20 hover:text-white active:scale-[0.97]">
                            <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                            Reset
                        </a>
                    @endif
                    <span class="ml-auto text-xs text-neutral-500">
                        {{ $cids->total() }} data ditemukan
                    </span>
                </div>
            </form>
        </div>


        <div class="slam-panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-white/5 bg-[#2a2a2a] text-[11px] uppercase tracking-[0.18em] text-neutral-500">
                        <tr>
                            <th class="px-6 py-4 font-medium">CID</th>
                            <th class="px-6 py-4 font-medium">Vendor</th>
                            <th class="px-6 py-4 font-medium">CID IS</th>
                            <th class="px-6 py-4 font-medium">Pelanggan</th>
                            <th class="px-6 py-4 font-medium">Service</th>
                            <th class="px-6 py-4 font-medium">Target SLA</th>
                            <th class="px-6 py-4 text-center font-medium">Ticket</th>
                            <th class="px-6 py-4 text-center font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($cids as $cid)
                            <tr class="transition duration-150 hover:bg-white/[0.03]">
                                <td class="px-6 py-4">
                                    <a href="{{ route('cids.show', $cid) }}" class="font-medium text-[#e66a4a] transition hover:text-[#ff7b5c]">
                                        {{ $cid->cid }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-neutral-200">{{ $cid->vendor_name }}</td>
                                <td class="px-6 py-4 text-neutral-200">
                                    @if($cid->cid_is)
                                        <a href="https://isn.nusa.net.id/customer.php?custId={{ $cid->cid_is }}&pid=profile&module=customer" target="_blank" class="font-medium text-[#e66a4a] transition hover:text-[#ff7b5c]">
                                            {{ $cid->cid_is }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-neutral-200">{{ $cid->customer_name }}</td>
                                <td class="px-6 py-4 text-neutral-400">{{ $cid->service }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-lg border border-white/5 bg-[#262626] px-2.5 py-1 text-sm tabular-nums text-white">
                                        {{ number_format((float) $cid->sla_percentage, 1) }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex h-7 min-w-[28px] items-center justify-center rounded-lg bg-[#262626] px-2 text-sm tabular-nums text-neutral-300">
                                        {{ $cid->tickets_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center items-center gap-2">
                                        <button @click="ticketCidId = {{ $cid->id }}; ticketCidCid = @js($cid->cid); ticketCidIs = @js($cid->cid_is); ticketCidVendor = @js($cid->vendor_name); ticketCidCustomer = @js($cid->customer_name); ticketCidService = @js($cid->service); showTicketModal = true" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-white/5 bg-[#262626] text-neutral-300 transition hover:border-white/10 hover:bg-[#2f2f2f] hover:text-white" title="Tambah Tiket" aria-label="Tambah Tiket">
                                            <span class="material-symbols-outlined text-[14px]">add_circle</span>
                                        </button>
                                        <button type="button" @click="editCid = { id: {{ $cid->id }}, cid: @js($cid->cid), cid_is: @js($cid->cid_is), vendor_name: @js($cid->vendor_name), customer_name: @js($cid->customer_name), service: @js($cid->service), sla_percentage: @js($cid->sla_percentage) }; showEditCidModal = true" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-white/5 bg-[#262626] text-neutral-300 transition hover:border-white/10 hover:bg-[#2f2f2f] hover:text-white" title="Edit CID" aria-label="Edit CID">
                                            <span class="material-symbols-outlined text-[14px]">edit</span>
                                        </button>
                                        <a href="{{ route('cids.show', $cid) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-white/5 bg-[#262626] text-neutral-300 transition hover:border-white/10 hover:bg-[#2f2f2f] hover:text-white" title="Detail CID" aria-label="Detail CID">
                                            <span class="material-symbols-outlined text-[14px]">visibility</span>
                                        </a>
                                        <form action="{{ route('cids.destroy', $cid) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus CID {{ $cid->cid }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-white/5 bg-[#262626] text-neutral-300 transition hover:border-red-900/50 hover:bg-red-900/20 hover:text-red-400" title="Hapus CID" aria-label="Hapus CID">
                                                <span class="material-symbols-outlined text-[14px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-white/5">
                                        <span class="material-symbols-outlined text-2xl text-neutral-500">database_off</span>
                                    </div>
                                    <p class="text-sm text-neutral-500">Belum ada data CID.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="border-t border-white/5 pt-4">
            {{ $cids->links() }}
        </div>

    {{-- Modal Tambah CID --}}
    <div x-show="showCidModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4"
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition duration-150 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:keydown.escape.window="showCidModal = false"
    >
        <div x-show="showCidModal" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/60 backdrop-blur-sm" x-on:click="showCidModal = false" aria-hidden="true"></div>

        <div x-show="showCidModal" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-6 opacity-0 scale-[0.97]" x-transition:enter-end="translate-y-0 opacity-100 scale-100" x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="translate-y-0 opacity-100 scale-100" x-transition:leave-end="translate-y-6 opacity-0 scale-[0.97]" class="relative z-10 w-full max-w-2xl">
            <div class="rounded-2xl border border-white/10 bg-[#1f1f1f] shadow-2xl shadow-black/50">
                {{-- Header --}}
                <div class="flex items-center gap-3 px-5 pb-2 pt-4">
                    <div class="flex items-center gap-1.5">
                        <button type="button" @click="showCidModal = false" class="h-3 w-3 rounded-full bg-[#ff5f57] ring-1 ring-black/20 transition hover:opacity-90" aria-label="Close modal"></button>
                        <span class="h-3 w-3 rounded-full bg-[#febc2e] ring-1 ring-black/20 opacity-60"></span>
                        <span class="h-3 w-3 rounded-full bg-[#28c840] ring-1 ring-black/20 opacity-60"></span>
                    </div>
                    <h3 class="text-sm font-semibold text-white/80">Tambah CID</h3>
                </div>

                {{-- Form --}}
                <form method="POST" action="{{ route('cids.store') }}" class="p-6">
                    @csrf
                    <input type="hidden" name="_modal" value="1">

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <x-input-label for="cid" value="CID" />
                            <x-text-input id="cid" name="cid" type="text" class="mt-1 block w-full" :value="old('cid')" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('cid')" />
                        </div>
                        <div>
                            <x-input-label for="cid_is" value="CID IS (ISN)" />
                            <x-text-input id="cid_is" name="cid_is" type="text" class="mt-1 block w-full" :value="old('cid_is')" />
                            <x-input-error class="mt-2" :messages="$errors->get('cid_is')" />
                        </div>
                        <div>
                            <x-input-label for="vendor_name" value="Nama Vendor" />
                            <x-text-input id="vendor_name" name="vendor_name" type="text" class="mt-1 block w-full" :value="old('vendor_name')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('vendor_name')" />
                        </div>
                        <div>
                            <x-input-label for="customer_name" value="Nama Pelanggan" />
                            <x-text-input id="customer_name" name="customer_name" type="text" class="mt-1 block w-full" :value="old('customer_name')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('customer_name')" />
                        </div>
                        <div>
                            <x-input-label for="service" value="Service" />
                            <x-text-input id="service" name="service" type="text" class="mt-1 block w-full" :value="old('service')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('service')" />
                        </div>
                        <div>
                            <x-input-label for="sla_percentage" value="SLA Target (%)" />
                            <x-text-input id="sla_percentage" name="sla_percentage" type="number" min="0" max="100" step="0.01" class="mt-1 block w-full" :value="old('sla_percentage', 99.00)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('sla_percentage')" />
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-3 border-t border-white/5 pt-5">
                        <button type="submit" class="inline-flex h-[42px] items-center gap-2 rounded-xl bg-[#e66a4a] px-6 text-sm font-medium text-white transition hover:bg-[#ff7b5c]">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            Simpan CID
                        </button>
                        <button type="button" @click="showCidModal = false" class="inline-flex h-[42px] items-center gap-2 rounded-xl border border-white/10 bg-[#262626] px-6 text-sm text-neutral-300 transition hover:bg-[#2f2f2f] hover:text-white">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Edit CID --}}
    <div x-show="showEditCidModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4"
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition duration-150 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:keydown.escape.window="showEditCidModal = false"
    >
        <div x-show="showEditCidModal" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/60 backdrop-blur-sm" x-on:click="showEditCidModal = false" aria-hidden="true"></div>

        <div x-show="showEditCidModal" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-6 opacity-0 scale-[0.97]" x-transition:enter-end="translate-y-0 opacity-100 scale-100" x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="translate-y-0 opacity-100 scale-100" x-transition:leave-end="translate-y-6 opacity-0 scale-[0.97]" class="relative z-10 w-full max-w-2xl">
            <div class="rounded-2xl border border-white/10 bg-[#1f1f1f] shadow-2xl shadow-black/50">
                <div class="flex items-center gap-3 px-5 pb-2 pt-4">
                    <div class="flex items-center gap-1.5">
                        <button type="button" @click="showEditCidModal = false" class="h-3 w-3 rounded-full bg-[#ff5f57] ring-1 ring-black/20 transition hover:opacity-90" aria-label="Close modal"></button>
                        <span class="h-3 w-3 rounded-full bg-[#febc2e] ring-1 ring-black/20 opacity-60"></span>
                        <span class="h-3 w-3 rounded-full bg-[#28c840] ring-1 ring-black/20 opacity-60"></span>
                    </div>
                    <h3 class="text-sm font-semibold text-white/80">Edit CID</h3>
                </div>

                <form method="POST" :action="`/cids/${editCid.id}`" class="p-6">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_modal" value="1">

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <x-input-label for="edit_cid" value="CID" />
                            <x-text-input id="edit_cid" name="cid" type="text" class="mt-1 block w-full" x-model="editCid.cid" required />
                            <x-input-error class="mt-2" :messages="$errors->get('cid')" />
                        </div>
                        <div>
                            <x-input-label for="edit_cid_is" value="CID IS (ISN)" />
                            <x-text-input id="edit_cid_is" name="cid_is" type="text" class="mt-1 block w-full" x-model="editCid.cid_is" />
                            <x-input-error class="mt-2" :messages="$errors->get('cid_is')" />
                        </div>
                        <div>
                            <x-input-label for="edit_vendor_name" value="Nama Vendor" />
                            <x-text-input id="edit_vendor_name" name="vendor_name" type="text" class="mt-1 block w-full" x-model="editCid.vendor_name" required />
                            <x-input-error class="mt-2" :messages="$errors->get('vendor_name')" />
                        </div>
                        <div>
                            <x-input-label for="edit_customer_name" value="Nama Pelanggan" />
                            <x-text-input id="edit_customer_name" name="customer_name" type="text" class="mt-1 block w-full" x-model="editCid.customer_name" required />
                            <x-input-error class="mt-2" :messages="$errors->get('customer_name')" />
                        </div>
                        <div>
                            <x-input-label for="edit_service" value="Service" />
                            <x-text-input id="edit_service" name="service" type="text" class="mt-1 block w-full" x-model="editCid.service" required />
                            <x-input-error class="mt-2" :messages="$errors->get('service')" />
                        </div>
                        <div>
                            <x-input-label for="edit_sla_percentage" value="SLA Target (%)" />
                            <x-text-input id="edit_sla_percentage" name="sla_percentage" type="number" min="0" max="100" step="0.01" class="mt-1 block w-full" x-model="editCid.sla_percentage" required />
                            <x-input-error class="mt-2" :messages="$errors->get('sla_percentage')" />
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-3 border-t border-white/5 pt-5">
                        <button type="submit" class="inline-flex h-[42px] items-center gap-2 rounded-xl bg-[#e66a4a] px-6 text-sm font-medium text-white transition hover:bg-[#ff7b5c]">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            Update CID
                        </button>
                        <button type="button" @click="showEditCidModal = false" class="inline-flex h-[42px] items-center gap-2 rounded-xl border border-white/10 bg-[#262626] px-6 text-sm text-neutral-300 transition hover:bg-[#2f2f2f] hover:text-white">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Buat Ticket --}}
    <div x-show="showTicketModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4"
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition duration-150 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:keydown.escape.window="showTicketModal = false"
    >
        <div x-show="showTicketModal" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/60 backdrop-blur-sm" x-on:click="showTicketModal = false" aria-hidden="true"></div>

        <div x-show="showTicketModal" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-6 opacity-0 scale-[0.97]" x-transition:enter-end="translate-y-0 opacity-100 scale-100" x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="translate-y-0 opacity-100 scale-100" x-transition:leave-end="translate-y-6 opacity-0 scale-[0.97]" class="relative z-10 w-full max-w-3xl">
            <div class="rounded-2xl border border-white/10 bg-[#1f1f1f] shadow-2xl shadow-black/50">
                {{-- Header --}}
                <div class="flex items-center gap-3 px-5 pb-2 pt-4">
                    <div class="flex items-center gap-1.5">
                        <button type="button" @click="showTicketModal = false" class="h-3 w-3 rounded-full bg-[#ff5f57] ring-1 ring-black/20 transition hover:opacity-90" aria-label="Close modal"></button>
                        <span class="h-3 w-3 rounded-full bg-[#febc2e] ring-1 ring-black/20 opacity-60"></span>
                        <span class="h-3 w-3 rounded-full bg-[#28c840] ring-1 ring-black/20 opacity-60"></span>
                    </div>
                    <h3 class="text-sm font-semibold text-white/80">Buat Ticket</h3>
                </div>

                {{-- Form --}}
                <form method="POST" action="{{ route('tickets.store') }}" class="p-6">
                    @csrf
                    <input type="hidden" name="cid_id" :value="ticketCidId">
                    <input type="hidden" name="_from_cid" value="1">

                    <div class="space-y-5">
                        <div class="rounded-2xl border border-white/5 bg-[#262626] p-4">
                            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.16em] text-neutral-500">Informasi CID</p>
                            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                                <div>
                                    <p class="text-[11px] uppercase tracking-[0.14em] text-neutral-500">CID</p>
                                    <p class="mt-1 truncate text-sm font-medium text-white" x-text="ticketCidCid">-</p>
                                </div>
                                <div>
                                    <p class="text-[11px] uppercase tracking-[0.14em] text-neutral-500">CID IS</p>
                                    <p class="mt-1 truncate text-sm font-medium text-white" x-text="ticketCidIs || '-'">-</p>
                                </div>
                                <div>
                                    <p class="text-[11px] uppercase tracking-[0.14em] text-neutral-500">Vendor</p>
                                    <p class="mt-1 truncate text-sm font-medium text-white" x-text="ticketCidVendor">-</p>
                                </div>
                                <div>
                                    <p class="text-[11px] uppercase tracking-[0.14em] text-neutral-500">Pelanggan</p>
                                    <p class="mt-1 truncate text-sm font-medium text-white" x-text="ticketCidCustomer">-</p>
                                </div>
                                <div class="md:col-span-2 xl:col-span-4">
                                    <p class="text-[11px] uppercase tracking-[0.14em] text-neutral-500">Service</p>
                                    <p class="mt-1 text-sm font-medium text-white" x-text="ticketCidService">-</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <x-input-label for="vendor_ticket_number" value="Ticket ID Vendor" />
                                <x-text-input id="vendor_ticket_number" name="vendor_ticket_number" type="text" class="mt-1 block w-full" :value="old('vendor_ticket_number')" />
                                <x-input-error class="mt-2" :messages="$errors->get('vendor_ticket_number')" />
                            </div>

                            <div>
                                <x-input-label for="case_type" value="Kasus" />
                                <div class="relative mt-1">
                                    <select id="case_type" name="case_type" class="block h-[42px] w-full appearance-none rounded-lg border border-neutral-700 bg-neutral-900 pr-10 text-neutral-100 shadow-sm focus:border-orange-500 focus:ring-orange-500/30" required>
                                        <option value="" class="bg-neutral-900">Pilih kasus</option>
                                        @foreach ($caseTypes as $caseType)
                                            <option value="{{ $caseType }}" class="bg-neutral-900" @selected(old('case_type') === $caseType)>{{ $caseType }}</option>
                                        @endforeach
                                    </select>
                                    <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-neutral-500">
                                        <span class="material-symbols-outlined text-[18px]">expand_more</span>
                                    </span>
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('case_type')" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="started_at" value="Waktu Mulai" />
                                <x-text-input id="started_at" name="started_at" type="datetime-local" class="mt-1 block w-full" :value="old('started_at')" required />
                                <x-input-error class="mt-2" :messages="$errors->get('started_at')" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-3 border-t border-white/5 pt-5">
                        <button type="submit" class="inline-flex h-[42px] items-center gap-2 rounded-xl bg-[#e66a4a] px-6 text-sm font-medium text-white transition hover:bg-[#ff7b5c]">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            Simpan Ticket
                        </button>
                        <button type="button" @click="showTicketModal = false" class="inline-flex h-[42px] items-center gap-2 rounded-xl border border-white/10 bg-[#262626] px-6 text-sm text-neutral-300 transition hover:bg-[#2f2f2f] hover:text-white">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>
</x-app-layout>
