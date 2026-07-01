<x-app-layout>
    <x-slot name="header">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
            <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">bar_chart</span>
        </div>
        <div>
            <h2 class="text-[22px] font-semibold tracking-tight text-white">SLA Bulanan</h2>
            <p class="mt-1 text-sm text-neutral-500">Ringkasan kepatuhan SLA per CID.</p>
        </div>
    </x-slot>

    <div class="slam-card p-6">
        <div class="overflow-hidden rounded-2xl border border-white/5 bg-[#151515]">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-white/5 text-[11px] uppercase tracking-[0.18em] text-neutral-500">
                    <tr>
                        <th class="px-6 py-4">CID</th>
                        <th class="px-6 py-4">Pelanggan</th>
                        <th class="px-6 py-4 text-right">Target</th>
                        <th class="px-6 py-4 text-right">SLA</th>
                        <th class="px-6 py-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach ($results as $item)
                        <tr class="transition duration-200 hover:bg-white/5">
                            <td class="px-6 py-4 font-medium text-white">{{ $item['cid'] }}</td>
                            <td class="px-6 py-4 text-neutral-300">{{ $item['customer_name'] }}</td>
                            <td class="px-6 py-4 text-right text-neutral-300">{{ number_format($item['sla_target'], 2) }}%</td>
                            <td class="px-6 py-4 text-right font-medium {{ $item['status'] === 'Aman' ? 'text-emerald-400' : 'text-red-400' }}">{{ number_format($item['sla_achieved'], 2) }}%</td>
                            <td class="px-6 py-4 text-center">
                                <span class="rounded-full px-3 py-1 text-xs {{ $item['status'] === 'Aman' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' }}">
                                    {{ $item['status'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
