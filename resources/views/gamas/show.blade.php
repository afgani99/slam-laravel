<x-app-layout>
    <x-slot name="header">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
            <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">sensors</span>
        </div>
        <div>
            <h2 class="text-[22px] font-semibold tracking-tight text-white">{{ $gamas->gamas_number }}</h2>
            <p class="mt-1 hidden text-sm text-neutral-500 sm:block">{{ __('gamas_show.subtitle') }}</p>
        </div>
    </x-slot>

    <div x-data="{ showGamasModal: false, modalAction: 'edit', reason: '', rfoAction: {{ json_encode($gamas->rfo_action ?? '') }}, pendingAt: '', resumeAt: '' }" class="space-y-6">
        <div class="slam-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-lg font-semibold text-white">{{ __('gamas_show.info_title') }}</p>
                    <p class="text-sm text-neutral-500">{{ __('gamas_show.info_subtitle') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    @if (!$gamas->isClosed() && in_array(auth()->user()->role, ['admin', 'operator']))
                        <button type="button" @click="modalAction = 'edit'; showGamasModal = true" class="inline-flex items-center gap-1.5 rounded-lg border border-white/5 bg-white/5 px-3 py-1.5 text-xs text-white transition hover:bg-white/10">
                            <span class="material-symbols-outlined text-[15px]">edit</span>
                            {{ __('gamas_show.edit') }}
                        </button>
                    @endif
                </div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="text-xs text-neutral-500">{{ __('gamas_show.gamas_number') }}</p>
                    <p class="mt-1 font-medium text-white">{{ $gamas->gamas_number }}</p>
                </div>
                <div>
                    <p class="text-xs text-neutral-500">{{ __('gamas_show.vendor_ticket') }}</p>
                    <p class="mt-1 font-medium text-white">{{ $gamas->vendor_ticket_number ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-neutral-500">{{ __('gamas_show.case_type') }}</p>
                    <p class="mt-1 font-medium text-white">{{ $gamas->case_type }}</p>
                </div>
                <div>
                    <p class="text-xs text-neutral-500">{{ __('gamas_show.status') }}</p>
                    <div class="mt-1">
                        @include('tickets._status-badge', ['status' => $gamas->status])
                    </div>
                </div>
                <div>
                    <p class="text-xs text-neutral-500">{{ __('gamas_show.started_at') }}</p>
                    <p class="mt-1 font-medium text-white">{{ $gamas->started_at->format('d M Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs text-neutral-500">{{ __('gamas_show.finished_at') }}</p>
                    <p class="mt-1 font-medium text-white">{{ $gamas->finished_at?->format('d M Y H:i') ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-neutral-500">{{ __('gamas_show.total_cid') }}</p>
                    <p class="mt-1 text-2xl font-semibold text-orange-400">{{ $gamas->tickets->count() }}</p>
                </div>
                <div>
                    <p class="text-xs text-neutral-500">{{ __('gamas_show.closed_cid') }}</p>
                    <p class="mt-1 text-2xl font-semibold text-emerald-400">{{ $closedTickets->count() }}</p>
                </div>
                <div>
                    <p class="text-xs text-neutral-500">{{ __('gamas_show.ticket_duration') }}</p>
                    <p class="mt-1 text-2xl font-semibold text-neutral-300">{{ $tiketDurasiFormatted ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-neutral-500">{{ __('gamas_show.effective_duration') }}</p>
                    <p class="mt-1 text-2xl font-semibold text-red-400">{{ $durasiFormatted ?? '-' }}</p>
                </div>
                @if ($pendingFormatted)
                    <div>
                        <p class="text-xs text-neutral-500">{{ __('gamas_show.pending_duration') }}</p>
                        <p class="mt-1 text-2xl font-semibold text-orange-400">{{ $pendingFormatted }}</p>
                    </div>
                @endif
            </div>

            @if ($gamas->rfo_action)
                <div class="mt-6 border-t border-white/5 pt-6">
                    <p class="text-xs text-neutral-500">{{ __('gamas_show.rfo_action') }}</p>
                    <p class="mt-2 text-sm text-neutral-300">{{ $gamas->rfo_action }}</p>
                </div>
            @endif

            @if (!empty($intervalLogs))
                <div class="mt-6 border-t border-white/5 pt-6">
                    <h3 class="mb-3 text-sm font-semibold text-white">{{ __('gamas_show.pending_history') }}</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-neutral-400">
                            <thead class="border-b border-white/5 uppercase text-neutral-600">
                                <tr>
                                    <th class="px-2 py-2">{{ __('gamas_show.started_at') }}</th>
                                    <th class="px-2 py-2">{{ __('gamas_show.finished_at') }}</th>
                                    <th class="px-2 py-2">{{ __('gamas_show.note') }}</th>
                                    @if(auth()->user()->role === 'admin')
                                        <th class="px-2 py-2">{{ __('gamas_show.action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @foreach ($intervalLogs as $interval)
                                    <tr>
                                        <td class="px-2 py-2">{{ $interval['pending']->started_at->format('d M H:i') }}</td>
                                        <td class="px-2 py-2">{{ $interval['resume']?->started_at?->format('d M H:i') ?: '-' }}</td>
                                        <td class="px-2 py-2">{{ $interval['pending']->reason ?: '-' }}</td>
                                        @if(auth()->user()->role === 'admin')
                                            <td class="px-2 py-2">
                                                <div class="flex items-center gap-2">
                                                    <form action="{{ route('gamas.logs.destroy', $interval['pending']) }}" method="POST" onsubmit="return confirm('{{ __('gamas_show.confirm_delete_pending') }}')" class="inline-block">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-white/5 bg-[#262626] text-neutral-300 transition hover:border-red-900/50 hover:bg-red-900/20 hover:text-red-400"
                                                            title="{{ __('gamas_show.delete_pending') }}">
                                                            <span class="material-symbols-outlined text-[14px]">delete</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        @if ($activeTickets->isNotEmpty())
            <div class="slam-card p-5">
                <h3 class="mb-3 text-sm font-semibold text-white">{{ __('gamas_show.affected_cid_report') }}</h3>
                <p class="mb-3 text-xs text-neutral-500">{{ __('gamas_show.active_ticket_count', ['count' => $activeTickets->count()]) }}</p>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-neutral-400">
                        <thead class="border-b border-white/5 uppercase text-neutral-600">
                            <tr>
                                <th class="px-2 py-2">{{ __('gamas_show.ticket_number') }}</th>
                                <th class="px-2 py-2">{{ __('gamas_show.cid') }}</th>
                                <th class="px-2 py-2">{{ __('gamas_show.vendor') }}</th>
                                <th class="px-2 py-2">{{ __('gamas_show.customer') }}</th>
                                <th class="px-2 py-2">{{ __('gamas_show.status') }}</th>
                                <th class="px-2 py-2 text-center">{{ __('gamas_show.action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach ($activeTickets as $ticket)
                                <tr>
                                    <td class="px-2 py-2">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="font-medium text-orange-400 hover:text-orange-300">{{ $ticket->ticket_number }}</a>
                                    </td>
                                    <td class="px-2 py-2 text-neutral-300">{{ $ticket->cid->cid }}</td>
                                    <td class="px-2 py-2 text-neutral-300">{{ $ticket->cid->vendor_name }}</td>
                                    <td class="px-2 py-2 text-neutral-300">{{ $ticket->cid->customer_name }}</td>
                                    <td class="px-2 py-2">@include('tickets._status-badge', ['status' => $ticket->status])</td>
                                    <td class="px-2 py-2 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('tickets.show', $ticket) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-white/5 bg-[#262626] text-neutral-300 transition hover:border-white/10 hover:bg-[#2f2f2f] hover:text-white" title="{{ __('gamas_show.detail_ticket') }}">
                                                <span class="material-symbols-outlined text-[14px]">visibility</span>
                                            </a>
                                            @if (!$gamas->isClosed() && auth()->user()->role === 'admin')
                                                <form action="{{ route('gamas.tickets.destroy', [$gamas, $ticket]) }}" method="POST" onsubmit="return confirm('{{ __('gamas_show.confirm_delete_ticket') }}')" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-white/5 bg-[#262626] text-neutral-300 transition hover:border-red-900/50 hover:bg-red-900/20 hover:text-red-400" title="{{ __('gamas_show.delete_ticket') }}">
                                                        <span class="material-symbols-outlined text-[14px]">delete</span>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if ($closedTickets->isNotEmpty())
            <div class="slam-card p-5">
                <h3 class="mb-3 text-sm font-semibold text-white">{{ __('gamas_show.closed_cid_report') }}</h3>
                <p class="mb-3 text-xs text-neutral-500">{{ __('gamas_show.closed_ticket_count', ['count' => $closedTickets->count()]) }}</p>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-neutral-400">
                        <thead class="border-b border-white/5 uppercase text-neutral-600">
                            <tr>
                                <th class="px-2 py-2">{{ __('gamas_show.ticket_number') }}</th>
                                <th class="px-2 py-2">{{ __('gamas_show.cid') }}</th>
                                <th class="px-2 py-2">{{ __('gamas_show.vendor') }}</th>
                                <th class="px-2 py-2">{{ __('gamas_show.customer') }}</th>
                                <th class="px-2 py-2">{{ __('gamas_show.finished_at') }}</th>
                                <th class="px-2 py-2 text-center">{{ __('gamas_show.action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach ($closedTickets as $ticket)
                                <tr>
                                    <td class="px-2 py-2">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="font-medium text-orange-400 hover:text-orange-300">{{ $ticket->ticket_number }}</a>
                                    </td>
                                    <td class="px-2 py-2 text-neutral-300">{{ $ticket->cid->cid }}</td>
                                    <td class="px-2 py-2 text-neutral-300">{{ $ticket->cid->vendor_name }}</td>
                                    <td class="px-2 py-2 text-neutral-300">{{ $ticket->cid->customer_name }}</td>
                                    <td class="px-2 py-2 text-neutral-400">{{ $ticket->finished_at?->format('d M H:i') }}</td>
                                    <td class="px-2 py-2 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('tickets.show', $ticket) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-white/5 bg-[#262626] text-neutral-300 transition hover:border-white/10 hover:bg-[#2f2f2f] hover:text-white" title="{{ __('gamas_show.detail_ticket') }}">
                                                <span class="material-symbols-outlined text-[14px]">visibility</span>
                                            </a>
                                            @if (auth()->user()->role === 'admin')
                                                <form action="{{ route('gamas.tickets.destroy', [$gamas, $ticket]) }}" method="POST" onsubmit="return confirm('{{ __('gamas_show.confirm_delete_ticket') }}')" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-white/5 bg-[#262626] text-neutral-300 transition hover:border-red-900/50 hover:bg-red-900/20 hover:text-red-400" title="{{ __('gamas_show.delete_ticket') }}">
                                                        <span class="material-symbols-outlined text-[14px]">delete</span>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Edit / Close / Pending Modal -->
        <div x-show="showGamasModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-on:keydown.escape.window="showGamasModal = false">
            <div x-show="showGamasModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm" x-on:click="showGamasModal = false" aria-hidden="true"></div>
            <div x-show="showGamasModal" class="relative z-10 w-full max-w-2xl" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-6 opacity-0 scale-[0.97]" x-transition:enter-end="translate-y-0 opacity-100 scale-100" x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="translate-y-0 opacity-100 scale-100" x-transition:leave-end="translate-y-6 opacity-0 scale-[0.97]">
                <div class="rounded-2xl border border-white/10 bg-[#1f1f1f] shadow-2xl shadow-black/50">
                    <div class="flex items-center gap-3 px-5 pb-2 pt-4">
                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="showGamasModal = false" class="h-3 w-3 rounded-full bg-[#ff5f57] ring-1 ring-black/20 transition hover:opacity-90" aria-label="Close modal"></button>
                            <span class="h-3 w-3 rounded-full bg-[#febc2e] ring-1 ring-black/20 opacity-60"></span>
                            <span class="h-3 w-3 rounded-full bg-[#28c840] ring-1 ring-black/20 opacity-60"></span>
                        </div>
                        <h3 class="text-sm font-semibold text-white/80" x-text="modalAction === 'edit' ? '{{ __('gamas_show.modal_edit') }}' : (modalAction === 'close' ? '{{ __('gamas_show.modal_close') }}' : (modalAction === 'pending' ? '{{ __('gamas_show.modal_pending') }}' : '{{ __('gamas_show.modal_resume') }}'))"></h3>
                    </div>

                    <form id="gamasModalForm" method="POST" x-bind:action="modalAction === 'close' ? '{{ route('gamas.close', $gamas) }}' : (modalAction === 'pending' ? '{{ route('gamas.pending', $gamas) }}' : (modalAction === 'resume' ? '{{ route('gamas.resume', $gamas) }}' : '{{ route('gamas.update', $gamas) }}'))" class="p-6">
                        @csrf
                        <input type="hidden" name="_method" x-bind:value="modalAction === 'edit' ? 'PUT' : 'POST'">

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <x-input-label for="modal_vendor_ticket_number" :value="__('gamas_show.vendor_ticket')" />
                                <x-text-input id="modal_vendor_ticket_number" name="vendor_ticket_number" type="text" class="mt-1 block w-full" :value="$gamas->vendor_ticket_number" />
                            </div>
                            <div>
                                <x-input-label for="modal_case_type" :value="__('gamas_show.case_type')" />
                                <div class="relative mt-1">
                                    <select id="modal_case_type" name="case_type" class="block h-[42px] w-full appearance-none rounded-lg border border-neutral-700 bg-neutral-900 pr-10 text-neutral-100 shadow-sm focus:border-orange-500 focus:ring-orange-500/30">
                                        @foreach ($caseTypes as $caseType)
                                            <option value="{{ $caseType }}" @selected($gamas->case_type === $caseType)>{{ $caseType }}</option>
                                        @endforeach
                                    </select>
                                    <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-neutral-500"><span class="material-symbols-outlined text-[18px]">expand_more</span></span>
                                </div>
                            </div>
                            <div>
                                <x-input-label for="modal_started_at" :value="__('gamas_show.started_at')" />
                                <x-text-input id="modal_started_at" name="started_at" type="datetime-local" class="mt-1 block w-full" :value="$gamas->started_at?->format('Y-m-d\TH:i')" />
                            </div>
                            <div>
                                <x-input-label for="modal_finished_at" :value="__('gamas_show.finished_at')" />
                                <x-text-input id="modal_finished_at" name="finished_at" type="datetime-local" class="mt-1 block w-full" :value="$gamas->finished_at?->format('Y-m-d\TH:i')" />
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label for="modal_rfo_action" :value="__('gamas_show.rfo_action')" />
                                <textarea id="modal_rfo_action" x-model="rfoAction" name="rfo_action" rows="3" class="mt-1 block w-full rounded-lg border border-neutral-700 bg-neutral-900 px-4 py-2 text-neutral-100 shadow-sm focus:border-orange-500 focus:ring-orange-500/30">{{ $gamas->rfo_action }}</textarea>
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label for="modal_reason" :value="__('gamas_show.pending_reason')" />
                                <textarea id="modal_reason" x-model="reason" name="reason" rows="3" class="mt-1 block w-full rounded-lg border border-neutral-700 bg-neutral-900 px-4 py-2 text-neutral-100 shadow-sm focus:border-orange-500 focus:ring-orange-500/30" placeholder="{{ __('gamas_show.pending_reason_placeholder') }}"></textarea>
                            </div>
                            @if ($gamas->isOpen())
                                <div>
                                    <x-input-label for="modal_pending_at" :value="__('gamas_show.pending_started_at')" />
                                    <x-text-input id="modal_pending_at" x-model="pendingAt" name="pending_at" type="datetime-local" class="mt-1 block w-full" />
                                </div>
                            @endif
                            @if ($gamas->isPending())
                                <div>
                                    <x-input-label for="modal_resume_at" :value="__('gamas_show.resume_at')" />
                                    <x-text-input id="modal_resume_at" x-model="resumeAt" name="resume_at" type="datetime-local" class="mt-1 block w-full" />
                                </div>
                            @endif
                        </div>

                        <div class="mt-6 flex items-center gap-3 border-t border-white/5 pt-5">
                            <button type="submit" @click="modalAction = 'edit'" class="inline-flex h-[42px] items-center gap-2 rounded-xl bg-[#e66a4a] px-6 text-sm font-medium text-white transition hover:bg-[#ff7b5c]">
                                <span class="material-symbols-outlined text-[18px]">save</span>
                                {{ __('gamas_show.save') }}
                            </button>
                            <button type="submit" @click="modalAction = 'close'" :disabled="rfoAction.trim() === ''" class="inline-flex h-[42px] items-center gap-2 rounded-xl bg-blue-500 px-6 text-sm font-medium text-white transition hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span class="material-symbols-outlined text-[18px]">task_alt</span>
                                {{ __('gamas_show.close_gamas') }}
                            </button>
                            @if ($gamas->isOpen())
                                <button type="submit" @click="modalAction = 'pending'" :disabled="reason.trim() === '' || pendingAt.trim() === ''" class="inline-flex h-[42px] items-center gap-2 rounded-xl bg-orange-500 px-6 text-sm font-medium text-white transition hover:bg-orange-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span class="material-symbols-outlined text-[18px]">pause</span>
                                    {{ __('gamas_show.set_pending') }}
                                </button>
                            @elseif ($gamas->isPending())
                                <button type="submit" @click="modalAction = 'resume'" :disabled="resumeAt.trim() === ''" class="inline-flex h-[42px] items-center gap-2 rounded-xl bg-emerald-500 px-6 text-sm font-medium text-white transition hover:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span class="material-symbols-outlined text-[18px]">play_arrow</span>
                                    {{ __('gamas_show.resume') }}
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
