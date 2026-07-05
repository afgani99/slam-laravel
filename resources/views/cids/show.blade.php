<x-app-layout>
    <x-slot name="header">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
            <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">hub</span>
        </div>
        <div>
            <h2 class="text-[22px] font-semibold tracking-tight text-white">{{ __('cid_show.title', ['cid' => $cid->cid]) }}</h2>
            <p class="mt-1 text-sm text-neutral-500">{{ __('cid_show.subtitle') }}</p>
        </div>
    </x-slot>

    <div class="space-y-4">
        <div class="grid gap-4 lg:grid-cols-2">
            <!-- Detail CID Card -->
            <div class="rounded-2xl border border-white/5 bg-[#262626] p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-lg bg-[#e66a4a]/10 px-2.5 py-1 text-sm font-semibold text-[#e66a4a]">{{ $cid->cid }}</span>
                            <span class="rounded-lg border border-white/5 bg-[#1f1f1f] px-2.5 py-1 text-xs text-neutral-300">{{ __('cid_show.sla_label') }} {{ number_format((float) $cid->sla_percentage, 2) }}%</span>
                        </div>
                        <div class="mt-4 space-y-2.5">
                            <div>
                                <p class="text-[11px] uppercase tracking-[0.14em] text-neutral-500">{{ __('cid_show.cid_is') }}</p>
                                <p class="mt-0.5 text-sm font-medium text-white">
                                    @if($cid->cid_is)
                                        <a href="https://isn.nusa.net.id/customer.php?custId={{ $cid->cid_is }}&pid=profile&module=customer" target="_blank" class="text-[#e66a4a] transition hover:text-[#ff7b5c] hover:underline">
                                            {{ $cid->cid_is }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-[11px] uppercase tracking-[0.14em] text-neutral-500">{{ __('cid_show.vendor') }}</p>
                                <p class="mt-0.5 text-sm font-medium text-white">{{ $cid->vendor_name }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] uppercase tracking-[0.14em] text-neutral-500">{{ __('cid_show.customer') }}</p>
                                <p class="mt-0.5 text-sm font-medium text-white">{{ $cid->customer_name }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] uppercase tracking-[0.14em] text-neutral-500">{{ __('cid_show.service') }}</p>
                                <p class="mt-0.5 text-sm font-medium text-white">{{ $cid->service }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2 border-t border-white/5 pt-4">
                    <div class="rounded-lg border border-white/5 bg-[#1f1f1f] px-3 py-2 text-center">
                        <p class="text-[10px] uppercase font-medium text-neutral-500">{{ __('cid_show.status_open') }}</p>
                        <p class="mt-1 text-lg font-bold text-emerald-400">{{ $cid->open_tickets_count }}</p>
                    </div>
                    <div class="rounded-lg border border-white/5 bg-[#1f1f1f] px-3 py-2 text-center">
                        <p class="text-[10px] uppercase font-medium text-neutral-500">{{ __('cid_show.status_pending') }}</p>
                        <p class="mt-1 text-lg font-bold text-yellow-400">{{ $cid->pending_tickets_count }}</p>
                    </div>
                    <div class="rounded-lg border border-white/5 bg-[#1f1f1f] px-3 py-2 text-center">
                        <p class="text-[10px] uppercase font-medium text-neutral-500">{{ __('cid_show.status_closed') }}</p>
                        <p class="mt-1 text-lg font-bold text-blue-400">{{ $cid->closed_tickets_count }}</p>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between border-t border-white/5 pt-4">
                    <p class="text-xs text-neutral-500">{{ __('cid_show.total_tickets') }}</p>
                    <p class="text-lg font-bold text-white">{{ $cid->tickets_count }}</p>
                </div>
            </div>

            <!-- SLA Chart Card -->
            <div class="rounded-2xl border border-white/5 bg-[#262626] p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-base font-semibold text-white">{{ __('cid_show.chart_title') }}</p>
                        <p class="mt-0.5 text-xs text-neutral-500">{{ __('cid_show.chart_subtitle') }}</p>
                    </div>
                    <a href="{{ route('cids.index') }}" class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-white/10 bg-[#1f1f1f] px-3 text-xs text-neutral-300 transition hover:bg-[#2f2f2f] hover:text-white">
                        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                    </a>
                </div>
                <div class="mt-3 h-[280px]">
                    <canvas id="cid-sla-chart" class="!h-full !w-full" data-labels='@json(collect($slaHistory)->pluck("label"))' data-values='@json(collect($slaHistory)->pluck("sla_achieved"))'></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Ticket Full Width -->
        <div class="rounded-2xl border border-white/5 bg-[#262626] p-4">
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <p class="text-base font-semibold text-white">{{ __('cid_show.recent_tickets_title') }}</p>
                    <p class="mt-0.5 text-xs text-neutral-500">{{ __('cid_show.recent_tickets_subtitle') }}</p>
                </div>
                @if ($recentTickets->count() >= 10)
                    <a href="{{ route('tickets.index', ['search' => $cid->cid]) }}" class="rounded-lg border border-white/10 bg-[#1f1f1f] px-3 py-1.5 text-xs text-[#e66a4a] transition hover:bg-[#2f2f2f] hover:text-[#ff7b5c]">{{ __('cid_show.show_more') }}</a>
                @endif
            </div>

            <div class="overflow-hidden rounded-xl border border-white/5 bg-[#1f1f1f]">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-white/5 bg-[#2a2a2a] text-[10px] uppercase tracking-[0.16em] text-neutral-500">
                        <tr>
                            <th class="px-4 py-3">{{ __('cid_show.col_ticket') }}</th>
                            <th class="px-4 py-3">{{ __('cid_show.col_case') }}</th>
                            <th class="px-4 py-3">{{ __('cid_show.col_status') }}</th>
                            <th class="px-4 py-3">{{ __('cid_show.col_started') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($recentTickets as $ticket)
                            <tr class="transition duration-150 hover:bg-white/[0.03]">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-white">{{ $ticket->ticket_number }}</p>
                                </td>
                                <td class="px-4 py-3 text-sm text-neutral-300">{{ $ticket->case_type }}</td>
                                <td class="px-4 py-3">@include('tickets._status-badge', ['status' => $ticket->status])</td>
                                <td class="whitespace-nowrap px-4 py-3 text-xs text-neutral-400">{{ $ticket->started_at?->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-neutral-500">{{ __('cid_show.no_tickets') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
