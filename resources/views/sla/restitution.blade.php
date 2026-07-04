<x-app-layout>
    <x-slot name="header">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
            <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">savings</span>
        </div>
        <div>
            <h2 class="text-[22px] font-semibold tracking-tight text-white">Restitusi</h2>
            <p class="mt-1 text-sm text-neutral-500">Daftar CID dengan pencapaian SLA di bawah target.</p>
        </div>
    </x-slot>

    @php
        $totalRestitusi = count($results);
        $yearOptions = range(now()->year, now()->year - 5);
    @endphp

    <div class="space-y-6">
        <div class="slam-panel p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
                        <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">filter_alt</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-white">Filter Periode</p>
                        <p class="text-xs text-neutral-500">Pilih bulan dan tahun perhitungan restitusi</p>
                    </div>
                </div>
                <span class="inline-flex h-9 items-center rounded-lg border border-white/5 bg-[#262626] px-3 text-xs text-neutral-400">
                    {{ $months[$month] ?? $month }} {{ $year }}
                </span>
            </div>

            <form method="GET" action="{{ route('sla.restitution') }}" class="mt-5 grid gap-3 lg:grid-cols-[1fr_160px_130px] lg:items-end">
                <div class="flex flex-col gap-1.5">
                    <x-input-label for="search" value="Pencarian" class="text-xs font-medium uppercase tracking-[0.15em] text-neutral-400" />
                    <div class="relative">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-neutral-500">search</span>
                        <x-text-input id="search" name="search" type="search" class="block w-full pl-10" placeholder="CID, vendor, pelanggan, atau service…" :value="request('search')" />
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <x-input-label for="bulan" value="Bulan" class="text-xs font-medium uppercase tracking-[0.15em] text-neutral-400" />
                    <select id="bulan" name="bulan" class="block w-full rounded-xl border border-white/10 bg-[#262626] px-4 py-2.5 text-sm text-white transition focus:border-[#e66a4a] focus:outline-none focus:ring-2 focus:ring-[#e66a4a]/20">
                        @foreach ($months as $value => $label)
                            <option value="{{ $value }}" @selected((int) $month === (int) $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1.5">
                    <x-input-label for="tahun" value="Tahun" class="text-xs font-medium uppercase tracking-[0.15em] text-neutral-400" />
                    <select id="tahun" name="tahun" class="block w-full rounded-xl border border-white/10 bg-[#262626] px-4 py-2.5 text-sm text-white transition focus:border-[#e66a4a] focus:outline-none focus:ring-2 focus:ring-[#e66a4a]/20">
                        @foreach ($yearOptions as $option)
                            <option value="{{ $option }}" @selected((int) $year === (int) $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2 border-t border-white/5 pt-4 lg:col-span-3">
                    <button type="submit" class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-[#e66a4a] px-4 text-sm font-medium text-white shadow-sm shadow-[#e66a4a]/20 transition hover:bg-[#ff7b5c] active:scale-[0.97]">
                        <span class="material-symbols-outlined text-[16px]">tune</span>
                        Terapkan Filter
                    </button>
                    @if (request()->has('search') || request()->has('bulan') || request()->has('tahun'))
                        <a href="{{ route('sla.restitution') }}" class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-white/10 bg-transparent px-4 text-sm text-neutral-400 transition hover:border-white/20 hover:text-white active:scale-[0.97]">
                            <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                            Reset
                        </a>
                    @endif
                    <span class="ml-auto text-xs text-neutral-500">
                        {{ $totalRestitusi }} data ditemukan
                    </span>
                    <a href="{{ route('sla.restitution.export', request()->query()) }}" class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-emerald-600 px-4 text-sm font-medium text-white shadow-sm transition hover:bg-emerald-700 active:scale-[0.97]">
                        <span class="material-symbols-outlined text-[16px]">download</span>
                        Export Excel
                    </a>
                </div>
            </form>
        </div>

        <div class="slam-panel overflow-hidden">
            <div class="flex items-center justify-between border-b border-white/5 px-6 py-4">
                <div>
                    <p class="text-sm font-medium text-white">Daftar Restitusi</p>
                    <p class="text-xs text-neutral-500">Detail SLA, downtime, dan target per CID</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-white/5 bg-[#2a2a2a] text-[11px] uppercase tracking-[0.18em] text-neutral-500">
                        <tr>
                            <th class="px-6 py-4 font-medium">CID</th>
                            <th class="px-6 py-4 font-medium">Vendor</th>
                            <th class="px-6 py-4 font-medium">Pelanggan</th>
                            <th class="px-6 py-4 font-medium">Service</th>
                            <th class="px-6 py-4 text-right font-medium">Target</th>
                            <th class="px-6 py-4 text-right font-medium">SLA</th>
                            <th class="px-6 py-4 text-right font-medium">Downtime</th>
                            <th class="px-6 py-4 text-right font-medium">Pending</th>
                            <th class="px-6 py-4 text-right font-medium">Efektif</th>
                            <th class="px-6 py-4 text-center font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($results as $item)
                            <tr class="transition duration-150 hover:bg-white/[0.03]">
                                <td class="px-6 py-4 font-medium text-[#e66a4a]">
                                    <a href="{{ route('cids.show', $item['id']) }}" class="transition hover:text-[#ff7b5c]">
                                        {{ $item['cid'] }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-neutral-200">{{ $item['vendor_name'] }}</td>
                                <td class="px-6 py-4 text-neutral-200">{{ $item['customer_name'] }}</td>
                                <td class="px-6 py-4 text-neutral-400">{{ $item['service'] }}</td>
                                <td class="px-6 py-4 text-right text-neutral-300">{{ number_format($item['sla_target'], 2) }}%</td>
                                <td class="px-6 py-4 text-right font-medium text-red-400">{{ number_format($item['sla_achieved'], 2) }}%</td>
                                <td class="px-6 py-4 text-right text-neutral-300">{{ number_format($item['total_downtime']) }} mnt</td>
                                <td class="px-6 py-4 text-right text-neutral-300">{{ number_format($item['total_pending']) }} mnt</td>
                                <td class="px-6 py-4 text-right text-neutral-300">{{ number_format($item['effective_downtime']) }} mnt</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex rounded-full bg-red-500/10 px-3 py-1 text-xs text-red-400">
                                        {{ $item['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-16 text-center">
                                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-white/5">
                                        <span class="material-symbols-outlined text-2xl text-neutral-500">verified</span>
                                    </div>
                                    <p class="text-sm text-neutral-400">Tidak ada CID yang perlu restitusi.</p>
                                    <p class="mt-1 text-xs text-neutral-500">Semua CID memenuhi target SLA pada periode ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
