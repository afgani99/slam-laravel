<x-app-layout>
    <x-slot name="header">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
            <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">bar_chart</span>
        </div>
        <div>
            <h2 class="text-[22px] font-semibold tracking-tight text-white">Dashboard</h2>
            <p class="mt-1 text-sm text-neutral-500">Manage your SLA connections and service health</p>
        </div>
    </x-slot>

    <div x-data="{ showCidModal: false, showTicketModal: false, ticketCidId: null, ticketCidCid: '', ticketCidIs: '', ticketCidVendor: '', ticketCidCustomer: '', ticketCidService: '', cidsList: @js($cids) }" class="space-y-8">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="text-sm font-medium text-neutral-400">Statistik Ringkasan</h3>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="showCidModal = true" class="inline-flex items-center gap-1.5 rounded-md bg-[#e66a4a] px-3 py-1.5 text-xs text-white shadow-sm transition hover:bg-[#ff7b5c]">
                    <span class="material-symbols-outlined text-[15px]">add</span>
                    Add CID
                </button>
                <button type="button" @click="showTicketModal = true" class="inline-flex items-center gap-1.5 rounded-md border border-white/5 bg-white/5 px-3 py-1.5 text-xs text-white transition hover:bg-white/10">
                    <span class="material-symbols-outlined text-[15px]">add</span>
                    Add Ticket
                </button>
                <div class="flex rounded-lg border border-white/5 bg-[#1a1a1a] p-1">
                    @php $filter = request('filter', 'month'); @endphp
                    <a href="{{ route('dashboard', ['filter' => 'day']) }}" 
                       class="px-3 py-1 text-xs rounded-md transition {{ $filter == 'day' ? 'bg-orange-500 text-white shadow-sm' : 'text-neutral-400 hover:text-white' }}">
                        Hari Ini
                    </a>
                    <a href="{{ route('dashboard', ['filter' => 'month']) }}" 
                       class="px-3 py-1 text-xs rounded-md transition {{ $filter == 'month' ? 'bg-orange-500 text-white shadow-sm' : 'text-neutral-400 hover:text-white' }}">
                        Bulan Ini
                    </a>
                    <a href="{{ route('dashboard', ['filter' => 'year']) }}" 
                       class="px-3 py-1 text-xs rounded-md transition {{ $filter == 'year' ? 'bg-orange-500 text-white shadow-sm' : 'text-neutral-400 hover:text-white' }}">
                        Tahun Ini
                    </a>
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <a href="{{ route('tickets.index', ['filter' => request('filter', 'month')]) }}" class="slam-card p-6 transition hover:bg-transparent"><p class="slam-label">Total Tiket Dibuat</p><div class="mt-4 text-[34px] font-semibold leading-none text-white">{{ $stats['opened_count'] + $stats['pending_count'] + $stats['closed_count'] }}</div></a>
            <a href="{{ route('tickets.index', ['status' => 'open', 'filter' => request('filter', 'month')]) }}" class="slam-card p-6 transition hover:bg-transparent"><p class="slam-label">Tiket Open</p><div class="mt-4 text-[34px] font-semibold leading-none text-emerald-400">{{ $stats['opened_count'] }}</div></a>
            <a href="{{ route('tickets.index', ['status' => 'pending', 'filter' => request('filter', 'month')]) }}" class="slam-card p-6 transition hover:bg-transparent"><p class="slam-label">Tiket Pending</p><div class="mt-4 text-[34px] font-semibold leading-none text-yellow-400">{{ $stats['pending_count'] }}</div></a>
            <a href="{{ route('tickets.index', ['status' => 'closed', 'filter' => request('filter', 'month')]) }}" class="slam-card p-6 transition hover:bg-transparent"><p class="slam-label">Tiket Closed</p><div class="mt-4 text-[34px] font-semibold leading-none text-blue-400">{{ $stats['closed_count'] }}</div></a>
            <a href="{{ route('tickets.index', ['status' => 'closed', 'filter' => request('filter', 'month')]) }}" class="slam-card p-6 transition hover:bg-transparent"><p class="slam-label">CID Restitusi</p><div class="mt-4 text-[34px] font-semibold leading-none text-red-400">{{ $stats['restitution_count'] }}</div></a>
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
            <div class="space-y-6">
                <div class="slam-panel p-6 hover:!bg-[#262626]">
                    <div class="flex items-center justify-between gap-4">
                        <div><p class="text-lg font-semibold text-white">Grafik Restitusi Bulanan</p><p class="text-sm text-neutral-500">Jumlah CID yang terkena restitusi per bulan</p></div>
                        <div class="text-xs text-neutral-500">Tahun {{ now()->year }}</div>
                    </div>
                    @php
                        $chartLabels = collect($monthlyRestitution)->pluck('month')->values();
                        $chartValues = collect($monthlyRestitution)->pluck('total')->values();
                    @endphp
                    <div class="mt-6 h-[300px]"><canvas id="monthly-restitution-chart" class="!h-full !w-full" data-labels='@json($chartLabels)' data-values='@json($chartValues)'></canvas></div>
                    <div class="mt-4 flex items-center justify-between border-t border-white/5 pt-4">
                        <div class="flex items-center gap-2 text-xs text-neutral-500"><span class="inline-block h-2 w-2 rounded-full bg-[#e66a4a]"></span>CID Restitusi</div>
                        <div class="text-xs text-neutral-500">Total: <span class="font-semibold text-white">{{ collect($monthlyRestitution)->sum('total') }}</span> CID terkena restitusi tahun ini</div>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="slam-panel p-6">
                        <div><p class="text-lg font-semibold text-white">Top CID Bermasalah</p><p class="text-sm text-neutral-500">CID dengan tiket terbanyak pada periode terpilih</p></div>
                        <div class="mt-5 space-y-3">
                            @forelse ($topCidIssues as $row)
                                <a href="{{ route('cids.show', $row->cid_id) }}" class="block rounded-2xl border border-white/5 bg-[#262626] px-4 py-3 transition hover:bg-[#2f2f2f]"><div class="flex items-center justify-between gap-4"><div><p class="font-medium text-white">{{ $row->cid?->cid ?? '-' }}</p><p class="text-xs text-neutral-500">{{ $row->cid?->customer_name ?? 'Unknown customer' }}</p></div><div class="text-right"><p class="text-lg font-semibold text-orange-400">{{ $row->total }}</p><p class="text-[11px] text-neutral-500">tickets</p></div></div></a>
                            @empty
                                <p class="text-sm text-neutral-500">Belum ada data CID bermasalah pada periode ini.</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="slam-panel p-6">
                        <div><p class="text-lg font-semibold text-white">Top Category Issue</p><p class="text-sm text-neutral-500">Kategori gangguan yang paling sering muncul</p></div>
                        <div class="mt-5 space-y-3">
                            @forelse ($topCategoryIssues as $row)
                                <a href="{{ route('tickets.index', ['case_type' => $row->case_type, 'filter' => request('filter', 'month')]) }}" class="block rounded-2xl border border-white/5 bg-[#262626] px-4 py-3 transition hover:bg-[#2f2f2f]"><div class="flex items-center justify-between gap-4"><div><p class="font-medium text-white">{{ $row->case_type ?? '-' }}</p><p class="text-xs text-neutral-500">Issue Category</p></div><div class="text-right"><p class="text-lg font-semibold text-emerald-400">{{ $row->total }}</p><p class="text-[11px] text-neutral-500">tickets</p></div></div></a>
                            @empty
                                <p class="text-sm text-neutral-500">Belum ada data kategori issue pada periode ini.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="slam-card p-5">
                <div class="flex items-center justify-between"><div><p class="text-sm font-semibold text-white">Recent Ticket</p><p class="text-xs text-neutral-500">Ticket terbaru pada periode terpilih</p></div><a href="{{ route('tickets.index', ['status' => 'open']) }}" class="rounded-full border border-white/5 bg-[#262626] px-3 py-1.5 text-[11px] text-neutral-400 transition hover:border-white/10 hover:bg-[#2f2f2f] hover:text-white">Details</a></div>
                <div class="mt-4 overflow-hidden rounded-2xl border border-white/5 bg-[#262626]">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-white/5 bg-[#2a2a2a] text-[11px] uppercase tracking-[0.18em] text-neutral-500"><tr><th class="px-4 py-3">Ticket</th><th class="px-4 py-3">CID</th><th class="px-4 py-3">When</th></tr></thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse ($recentTickets as $ticket)
                                <tr class="transition duration-200 hover:bg-white/[0.03]"><td class="px-4 py-3 text-neutral-100">{{ $ticket->ticket_number }}</td><td class="px-4 py-3 text-neutral-400">{{ $ticket->cid?->cid ?? '-' }}</td><td class="px-4 py-3 text-neutral-500">{{ $ticket->created_at?->diffForHumans() }}</td></tr>
                            @empty
                                <tr><td colspan="3" class="px-4 py-6 text-center text-sm text-neutral-500">Belum ada tiket pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div x-show="showCidModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-on:keydown.escape.window="showCidModal = false">
            <div x-show="showCidModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm" x-on:click="showCidModal = false" aria-hidden="true"></div>
            <div x-show="showCidModal" class="relative z-10 w-full max-w-2xl" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-6 opacity-0 scale-[0.97]" x-transition:enter-end="translate-y-0 opacity-100 scale-100" x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="translate-y-0 opacity-100 scale-100" x-transition:leave-end="translate-y-6 opacity-0 scale-[0.97]">
                <div class="rounded-2xl border border-white/10 bg-[#1f1f1f] shadow-2xl shadow-black/50">
                    <div class="flex items-center gap-3 px-5 pb-2 pt-4"><div class="flex items-center gap-1.5"><button type="button" @click="showCidModal = false" class="h-3 w-3 rounded-full bg-[#ff5f57] ring-1 ring-black/20 transition hover:opacity-90" aria-label="Close modal"></button><span class="h-3 w-3 rounded-full bg-[#febc2e] ring-1 ring-black/20 opacity-60"></span><span class="h-3 w-3 rounded-full bg-[#28c840] ring-1 ring-black/20 opacity-60"></span></div><h3 class="text-sm font-semibold text-white/80">Tambah CID</h3></div>
                    <form method="POST" action="{{ route('cids.store') }}" class="p-6">
                        @csrf
                        <input type="hidden" name="_modal" value="1">
                        <div class="grid gap-5 md:grid-cols-2">
                            <div><x-input-label for="dashboard_cid" value="CID" /><x-text-input id="dashboard_cid" name="cid" type="text" class="mt-1 block w-full" :value="old('cid')" required autofocus /><x-input-error class="mt-2" :messages="$errors->get('cid')" /></div>
                            <div><x-input-label for="dashboard_cid_is" value="CID IS (ISN)" /><x-text-input id="dashboard_cid_is" name="cid_is" type="text" class="mt-1 block w-full" :value="old('cid_is')" /><x-input-error class="mt-2" :messages="$errors->get('cid_is')" /></div>
                            <div><x-input-label for="dashboard_vendor_name" value="Nama Vendor" /><x-text-input id="dashboard_vendor_name" name="vendor_name" type="text" class="mt-1 block w-full" :value="old('vendor_name')" required /><x-input-error class="mt-2" :messages="$errors->get('vendor_name')" /></div>
                            <div><x-input-label for="dashboard_customer_name" value="Nama Pelanggan" /><x-text-input id="dashboard_customer_name" name="customer_name" type="text" class="mt-1 block w-full" :value="old('customer_name')" required /><x-input-error class="mt-2" :messages="$errors->get('customer_name')" /></div>
                            <div><x-input-label for="dashboard_service" value="Service" /><x-text-input id="dashboard_service" name="service" type="text" class="mt-1 block w-full" :value="old('service')" required /><x-input-error class="mt-2" :messages="$errors->get('service')" /></div>
                            <div><x-input-label for="dashboard_sla_percentage" value="SLA Target (%)" /><x-text-input id="dashboard_sla_percentage" name="sla_percentage" type="number" min="0" max="100" step="0.01" class="mt-1 block w-full" :value="old('sla_percentage', 99.00)" required /><x-input-error class="mt-2" :messages="$errors->get('sla_percentage')" /></div>
                        </div>
                        <div class="mt-6 flex items-center gap-3 border-t border-white/5 pt-5"><button type="submit" class="inline-flex h-[42px] items-center gap-2 rounded-xl bg-[#e66a4a] px-6 text-sm font-medium text-white transition hover:bg-[#ff7b5c]"><span class="material-symbols-outlined text-[18px]">save</span>Simpan CID</button><button type="button" @click="showCidModal = false" class="inline-flex h-[42px] items-center gap-2 rounded-xl border border-white/10 bg-[#262626] px-6 text-sm text-neutral-300 transition hover:bg-[#2f2f2f] hover:text-white">Batal</button></div>
                    </form>
                </div>
            </div>
        </div>
        <div x-show="showTicketModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-on:keydown.escape.window="showTicketModal = false">
            <div x-show="showTicketModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm" x-on:click="showTicketModal = false" aria-hidden="true"></div>
            <div x-show="showTicketModal" class="relative z-10 w-full max-w-3xl" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-6 opacity-0 scale-[0.97]" x-transition:enter-end="translate-y-0 opacity-100 scale-100" x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="translate-y-0 opacity-100 scale-100" x-transition:leave-end="translate-y-6 opacity-0 scale-[0.97]" x-on:click.outside="showTicketModal = false">
                <div class="rounded-2xl border border-white/10 bg-[#1f1f1f] shadow-2xl shadow-black/50">
                    <div class="flex items-center gap-3 px-5 pb-2 pt-4"><div class="flex items-center gap-1.5"><button type="button" @click="showTicketModal = false" class="h-3 w-3 rounded-full bg-[#ff5f57] ring-1 ring-black/20 transition hover:opacity-90" aria-label="Close modal"></button><span class="h-3 w-3 rounded-full bg-[#febc2e] ring-1 ring-black/20 opacity-60"></span><span class="h-3 w-3 rounded-full bg-[#28c840] ring-1 ring-black/20 opacity-60"></span></div><h3 class="text-sm font-semibold text-white/80">Buat Ticket</h3></div>
                    <form method="POST" action="{{ route('tickets.store') }}" class="p-6">
                        @csrf
                        <div class="grid gap-5 md:grid-cols-2">
                            <div x-data="{ search: '', open: false }" class="md:col-span-2">
                                <x-input-label for="ticket_dashboard_cid_id" value="Pilih CID" />
                                <div class="relative mt-1">
                                    <x-text-input type="text" x-model="search" @focus="open = true" @click.away="open = false" placeholder="Cari CID / Nama Pelanggan / CID IS..." class="w-full" autocomplete="off" />
                                    <input type="hidden" name="cid_id" x-model="ticketCidId">
                                    <ul x-show="open" x-cloak class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-neutral-700 bg-neutral-900 py-1 shadow-xl">
                                        <template x-for="cid in cidsList.filter(c => c.cid.toLowerCase().includes(search.toLowerCase()) || (c.customer_name && c.customer_name.toLowerCase().includes(search.toLowerCase())) || (c.cid_is && c.cid_is.toLowerCase().includes(search.toLowerCase())))" :key="cid.id">
                                            <li @click="ticketCidId = cid.id; ticketCidCid = cid.cid; ticketCidIs = cid.cid_is || ''; ticketCidVendor = cid.vendor_name || ''; ticketCidCustomer = cid.customer_name || ''; ticketCidService = cid.service || ''; search = cid.cid; open = false;" class="cursor-pointer px-4 py-2 text-sm text-neutral-100 hover:bg-neutral-800">
                                                <div class="font-medium" x-text="cid.cid"></div>
                                                <div class="text-[11px] text-neutral-400"><span x-text="cid.cid_is || '-'"></span> | <span x-text="cid.customer_name || '-'"></span></div>
                                            </li>
                                        </template>
                                        <li x-show="cidsList.filter(c => c.cid.toLowerCase().includes(search.toLowerCase()) || (c.customer_name && c.customer_name.toLowerCase().includes(search.toLowerCase())) || (c.cid_is && c.cid_is.toLowerCase().includes(search.toLowerCase()))).length === 0" class="px-4 py-2 text-sm text-neutral-500">Tidak ditemukan</li>
                                    </ul>
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('cid_id')" />
                            </div>

                            <div>
                                <x-input-label for="ticket_dashboard_vendor_name" value="Nama Vendor" />
                                <x-text-input id="ticket_dashboard_vendor_name" type="text" class="mt-1 block w-full" x-bind:value="ticketCidVendor" readonly />
                            </div>
                            <div>
                                <x-input-label for="ticket_dashboard_cid_is" value="CID IS" />
                                <x-text-input id="ticket_dashboard_cid_is" type="text" class="mt-1 block w-full" x-bind:value="ticketCidIs" readonly />
                            </div>
                            <div>
                                <x-input-label for="ticket_dashboard_customer_name" value="Nama Pelanggan" />
                                <x-text-input id="ticket_dashboard_customer_name" type="text" class="mt-1 block w-full" x-bind:value="ticketCidCustomer" readonly />
                            </div>
                            <div>
                                <x-input-label for="ticket_dashboard_service" value="Service" />
                                <x-text-input id="ticket_dashboard_service" type="text" class="mt-1 block w-full" x-bind:value="ticketCidService" readonly />
                            </div>

                            <div>
                                <x-input-label for="ticket_dashboard_vendor_ticket_number" value="Ticket ID Vendor" />
                                <x-text-input id="ticket_dashboard_vendor_ticket_number" name="vendor_ticket_number" type="text" class="mt-1 block w-full" :value="old('vendor_ticket_number')" />
                                <x-input-error class="mt-2" :messages="$errors->get('vendor_ticket_number')" />
                            </div>
                            <div>
                                <x-input-label for="ticket_dashboard_case_type" value="Kasus" />
                                <div class="relative mt-1">
                                    <select id="ticket_dashboard_case_type" name="case_type" class="block h-[42px] w-full appearance-none rounded-lg border border-neutral-700 bg-neutral-900 pr-10 text-neutral-100 shadow-sm focus:border-orange-500 focus:ring-orange-500/30" required>
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
                                <x-input-label for="ticket_dashboard_started_at" value="Waktu Mulai" />
                                <x-text-input id="ticket_dashboard_started_at" name="started_at" type="datetime-local" class="mt-1 block w-full" :value="old('started_at')" required />
                                <x-input-error class="mt-2" :messages="$errors->get('started_at')" />
                            </div>
                        </div>
                        <div class="mt-6 flex items-center gap-3 border-t border-white/5 pt-5">
                            <button type="submit" class="inline-flex h-[42px] items-center gap-2 rounded-xl bg-[#e66a4a] px-6 text-sm font-medium text-white transition hover:bg-[#ff7b5c]"><span class="material-symbols-outlined text-[18px]">save</span>Simpan Ticket</button>
                            <button type="button" @click="showTicketModal = false" class="inline-flex h-[42px] items-center gap-2 rounded-xl border border-white/10 bg-[#262626] px-6 text-sm text-neutral-300 transition hover:bg-[#2f2f2f] hover:text-white">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
