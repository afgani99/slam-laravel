<x-app-layout>
    <x-slot name="header">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
            <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">notifications_active</span>
        </div>
        <div>
            <h2 class="text-[22px] font-semibold tracking-tight text-white">{{ __('tickets.title') }}</h2>
            <p class="mt-1 text-sm text-neutral-500">{{ __('tickets.subtitle') }}</p>
        </div>
    </x-slot>

    <div class="space-y-6"
        x-data="{ showTicketModal: false, ticketCidId: null, ticketCidCid: '', ticketCidIs: '', ticketCidVendor: '', ticketCidCustomer: '', ticketCidService: '', cidsList: @js($cids) }">

        {{-- Search & Filter Card --}}
        <div class="slam-panel p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
                        <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">filter_alt</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-white">{{ __('tickets.filter_title') }}</p>
                        <p class="text-xs text-neutral-500">{{ __('tickets.filter_subtitle') }}</p>
                    </div>
                </div>
                @if(in_array(auth()->user()->role, ['admin', 'operator']))
                    <button type="button" @click="showTicketModal = true" class="slam-primary-btn shrink-0">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        {{ __('tickets.create_ticket') }}
                    </button>
                @endif
            </div>

            <form method="GET" action="{{ route('tickets.index') }}"
                class="mt-5 grid gap-3 lg:grid-cols-[1fr_110px_160px_120px] lg:items-end">

                {{-- Search Input --}}
                <div class="flex flex-col gap-1.5">
                    <x-input-label for="search" :value="__('tickets.search')" class="text-xs font-medium uppercase tracking-[0.15em] text-neutral-400" />
                    <div class="relative">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-neutral-500">search</span>
                        <x-text-input id="search" name="search" type="search" class="block w-full pl-10"
                            :placeholder="__('tickets.search_placeholder')" :value="$search" />
                    </div>
                </div>

                {{-- Per Page --}}
                <div class="flex flex-col gap-1.5">
                    <x-input-label for="per_page" :value="__('tickets.per_page')" class="text-xs font-medium uppercase tracking-[0.15em] text-neutral-400" />
                    <select id="per_page" name="per_page"
                        class="block w-full rounded-xl border border-white/10 bg-[#262626] px-4 py-2.5 text-sm text-white transition focus:border-[#e66a4a] focus:outline-none focus:ring-2 focus:ring-[#e66a4a]/20">
                        @foreach ([10, 25, 50] as $option)
                            <option value="{{ $option }}" @selected($perPage === $option)>{{ __('tickets.rows', ['count' => $option]) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="flex flex-col gap-1.5">
                    <x-input-label for="status" :value="__('tickets.status')" class="text-xs font-medium uppercase tracking-[0.15em] text-neutral-400" />
                    <select id="status" name="status"
                        class="block w-full rounded-xl border border-white/10 bg-[#262626] px-4 py-2.5 text-sm text-white transition focus:border-[#e66a4a] focus:outline-none focus:ring-2 focus:ring-[#e66a4a]/20">
                        <option value="">{{ __('tickets.all_status') }}</option>
                        <option value="open"      {{ $status === 'open'      ? 'selected' : '' }}>Opened</option>
                        <option value="pending"   {{ $status === 'pending'   ? 'selected' : '' }}>Pending</option>
                        <option value="closed"    {{ $status === 'closed'    ? 'selected' : '' }}>Closed</option>
                        <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                {{-- Period --}}
                <div class="flex flex-col gap-1.5">
                    <x-input-label for="filter" value="Periode" class="text-xs font-medium uppercase tracking-[0.15em] text-neutral-400" />
                    <select id="filter" name="filter"
                        class="block w-full rounded-xl border border-white/10 bg-[#262626] px-4 py-2.5 text-sm text-white transition focus:border-[#e66a4a] focus:outline-none focus:ring-2 focus:ring-[#e66a4a]/20">
                        <option value="day"   {{ $filter === 'day'   ? 'selected' : '' }}>Hari Ini</option>
                        <option value="month" {{ $filter === 'month' ? 'selected' : '' }}>Bulan Ini</option>
                        <option value="year"  {{ $filter === 'year'  ? 'selected' : '' }}>Tahun Ini</option>
                    </select>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2 border-t border-white/5 pt-4 lg:col-span-4">
                    <button type="submit"
                        class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-[#e66a4a] px-4 text-sm font-medium text-white shadow-sm shadow-[#e66a4a]/20 transition hover:bg-[#ff7b5c] active:scale-[0.97]">
                        <span class="material-symbols-outlined text-[16px]">tune</span>
                        {{ __('tickets.apply_filter') }}
                    </button>
                    @if (request()->has('search'))
                        <a href="{{ route('tickets.index') }}"
                            class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-white/10 bg-transparent px-4 text-sm text-neutral-400 transition hover:border-white/20 hover:text-white active:scale-[0.97]">
                            <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                            {{ __('tickets.reset') }}
                        </a>
                    @endif
                    <span class="ml-auto text-xs text-neutral-500">
                        {{ __('tickets.total_found', ['total' => $tickets->total()]) }}
                    </span>
                </div>
            </form>
        </div>

        {{-- Data Table --}}
        <div class="slam-panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-white/5 bg-[#2a2a2a] text-[11px] uppercase tracking-[0.18em] text-neutral-500">
                        <tr>
                            <th class="px-6 py-4 font-medium">{{ __('tickets.ticket_number') }}</th>
                            <th class="px-6 py-4 font-medium">{{ __('tickets.cid') }}</th>
                            <th class="px-6 py-4 font-medium">{{ __('tickets.vendor') }}</th>
                            <th class="px-6 py-4 font-medium">{{ __('tickets.customer') }}</th>
                            <th class="px-6 py-4 font-medium">{{ __('tickets.case') }}</th>
                            <th class="px-6 py-4 font-medium">{{ __('tickets.status') }}</th>
                            <th class="px-6 py-4 font-medium">{{ __('tickets.started') }}</th>
                            @if(auth()->user()->role === 'admin')
                                <th class="px-6 py-4 text-right font-medium">{{ __('tickets.action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($tickets as $ticket)
                            <tr class="transition duration-150 hover:bg-white/[0.03]">
                                <td class="px-6 py-4">
                                    <a href="{{ route('tickets.show', $ticket) }}"
                                        class="font-medium text-[#e66a4a] transition hover:text-[#ff7b5c]">
                                        {{ $ticket->ticket_number }}
                                    </a>
                                    @if ($ticket->gamas_id)
                                        <a href="{{ route('gamas.show', $ticket->gamas_id) }}"
                                            class="mt-1 inline-block rounded px-1 py-px text-[10px] font-semibold uppercase tracking-wide text-[#e66a4a] ring-1 ring-[#e66a4a]/30 transition hover:bg-[#e66a4a]/10">
                                            gamas
                                        </a>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-neutral-300">{{ $ticket->cid->cid }}</td>
                                <td class="px-6 py-4 text-neutral-300">{{ $ticket->cid->vendor_name ?: '-' }}</td>
                                <td class="px-6 py-4 text-neutral-300">{{ $ticket->cid->customer_name }}</td>
                                <td class="px-6 py-4 text-neutral-300">{{ $ticket->case_type }}</td>
                                <td class="px-6 py-4">@include('tickets._status-badge', ['status' => $ticket->status])</td>
                                <td class="px-6 py-4 text-neutral-400">{{ $ticket->started_at?->format('d M H:i') }}</td>
                                @if(auth()->user()->role === 'admin')
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <form action="{{ route('tickets.destroy', $ticket) }}" method="POST"
                                                onsubmit="return confirm('{{ __('tickets.confirm_delete') }}')" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-white/5 bg-[#262626] text-neutral-300 transition hover:border-red-900/50 hover:bg-red-900/20 hover:text-red-400"
                                                    title="{{ __('tickets.delete') }}">
                                                    <span class="material-symbols-outlined text-[14px]">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-16 text-center">
                                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-white/5">
                                        <span class="material-symbols-outlined text-2xl text-neutral-500">inbox</span>
                                    </div>
                                    <p class="text-sm text-neutral-500">{{ __('tickets.no_data') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="border-t border-white/5 pt-4">
            {{ $tickets->links() }}
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
            x-on:keydown.escape.window="showTicketModal = false">

            {{-- Backdrop --}}
            <div x-show="showTicketModal"
                class="fixed inset-0 bg-black/60 backdrop-blur-sm"
                x-on:click="showTicketModal = false"
                aria-hidden="true">
            </div>

            {{-- Modal Panel --}}
            <div x-show="showTicketModal"
                class="relative z-10 w-full max-w-3xl"
                x-transition:enter="transition duration-200 ease-out"
                x-transition:enter-start="translate-y-6 opacity-0 scale-[0.97]"
                x-transition:enter-end="translate-y-0 opacity-100 scale-100"
                x-transition:leave="transition duration-150 ease-in"
                x-transition:leave-start="translate-y-0 opacity-100 scale-100"
                x-transition:leave-end="translate-y-6 opacity-0 scale-[0.97]">

                <div class="rounded-2xl border border-white/10 bg-[#1f1f1f] shadow-2xl shadow-black/50">
                    {{-- macOS-style header --}}
                    <div class="flex items-center gap-3 px-5 pb-2 pt-4">
                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="showTicketModal = false"
                                class="h-3 w-3 rounded-full bg-[#ff5f57] ring-1 ring-black/20 transition hover:opacity-90"
                                :aria-label="__('tickets.cancel')"></button>
                            <span class="h-3 w-3 rounded-full bg-[#febc2e] ring-1 ring-black/20 opacity-60"></span>
                            <span class="h-3 w-3 rounded-full bg-[#28c840] ring-1 ring-black/20 opacity-60"></span>
                        </div>
                        <h3 class="text-sm font-semibold text-white/80">{{ __('tickets.modal_create_title') }}</h3>
                    </div>

                    {{-- Form --}}
                    <form method="POST" action="{{ route('tickets.store') }}" class="p-6">
                        @csrf

                        <div class="grid gap-5 md:grid-cols-2">
                            {{-- CID searchable --}}
                            <div x-data="{ search: '', open: false }" class="md:col-span-2">
                                <x-input-label for="ticket_index_cid_search" :value="__('tickets.select_cid')" />
                                <div class="relative mt-1">
                                    <x-text-input
                                        id="ticket_index_cid_search"
                                        type="text"
                                        x-model="search"
                                        @focus="open = true"
                                        @click.away="open = false"
                                        :placeholder="__('tickets.search_cid_placeholder')"
                                        class="w-full"
                                        autocomplete="off" />
                                    <input type="hidden" name="cid_id" x-model="ticketCidId">
                                    <ul x-show="open" x-cloak
                                        class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-neutral-700 bg-neutral-900 py-1 shadow-xl">
                                        <template x-for="cid in cidsList.filter(c =>
                                            c.cid.toLowerCase().includes(search.toLowerCase()) ||
                                            (c.customer_name && c.customer_name.toLowerCase().includes(search.toLowerCase())) ||
                                            (c.cid_is && c.cid_is.toLowerCase().includes(search.toLowerCase()))
                                        )" :key="cid.id">
                                            <li @click="ticketCidId = cid.id; ticketCidCid = cid.cid; ticketCidIs = cid.cid_is || ''; ticketCidVendor = cid.vendor_name || ''; ticketCidCustomer = cid.customer_name || ''; ticketCidService = cid.service || ''; search = cid.cid; open = false;"
                                                class="cursor-pointer px-4 py-2 text-sm text-neutral-100 hover:bg-neutral-800">
                                                <div class="font-medium" x-text="cid.cid"></div>
                                                <div class="text-[11px] text-neutral-400">
                                                    <span x-text="cid.cid_is || '-'"></span> | <span x-text="cid.customer_name || '-'"></span>
                                                </div>
                                            </li>
                                        </template>
                                        <li x-show="cidsList.filter(c =>
                                            c.cid.toLowerCase().includes(search.toLowerCase()) ||
                                            (c.customer_name && c.customer_name.toLowerCase().includes(search.toLowerCase())) ||
                                            (c.cid_is && c.cid_is.toLowerCase().includes(search.toLowerCase()))
                                        ).length === 0"
                                            class="px-4 py-2 text-sm text-neutral-500">{{ __('tickets.no_cid_found') }}</li>
                                    </ul>
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('cid_id')" />
                            </div>

                            {{-- Info readonly CID --}}
                            <div>
                                <x-input-label :value="__('tickets.vendor_name')" />
                                <x-text-input type="text" class="mt-1 block w-full" x-bind:value="ticketCidVendor" readonly />
                            </div>
                            <div>
                                <x-input-label value="CID IS" />
                                <x-text-input type="text" class="mt-1 block w-full" x-bind:value="ticketCidIs" readonly />
                            </div>
                            <div>
                                <x-input-label :value="__('tickets.customer_name')" />
                                <x-text-input type="text" class="mt-1 block w-full" x-bind:value="ticketCidCustomer" readonly />
                            </div>
                            <div>
                                <x-input-label :value="__('tickets.service')" />
                                <x-text-input type="text" class="mt-1 block w-full" x-bind:value="ticketCidService" readonly />
                            </div>

                            {{-- Ticket ID Vendor --}}
                            <div>
                                <x-input-label for="ticket_index_vendor_ticket_number" :value="__('tickets.vendor_ticket_id')" />
                                <x-text-input id="ticket_index_vendor_ticket_number" name="vendor_ticket_number"
                                    type="text" class="mt-1 block w-full" :value="old('vendor_ticket_number')" />
                                <x-input-error class="mt-2" :messages="$errors->get('vendor_ticket_number')" />
                            </div>

                            {{-- Kasus --}}
                            <div>
                                <x-input-label for="ticket_index_case_type" :value="__('tickets.case')" />
                                <div class="relative mt-1">
                                    <select id="ticket_index_case_type" name="case_type"
                                        class="block h-[42px] w-full appearance-none rounded-lg border border-neutral-700 bg-neutral-900 pr-10 text-neutral-100 shadow-sm focus:border-orange-500 focus:ring-orange-500/30"
                                        required>
                                        <option value="" class="bg-neutral-900">{{ __('tickets.case_placeholder') }}</option>
                                        @foreach ($caseTypes as $ct)
                                            <option value="{{ $ct }}" class="bg-neutral-900" @selected(old('case_type') === $ct)>{{ $ct }}</option>
                                        @endforeach
                                    </select>
                                    <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-neutral-500">
                                        <span class="material-symbols-outlined text-[18px]">expand_more</span>
                                    </span>
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('case_type')" />
                            </div>

                            {{-- Waktu Mulai --}}
                            <div class="md:col-span-2">
                                <x-input-label for="ticket_index_started_at" :value="__('tickets.started_at')" />
                                <x-text-input id="ticket_index_started_at" name="started_at"
                                    type="datetime-local" class="mt-1 block w-full" :value="old('started_at')" required />
                                <x-input-error class="mt-2" :messages="$errors->get('started_at')" />
                            </div>
                        </div>

                        <div class="mt-6 flex items-center gap-3 border-t border-white/5 pt-5">
                            <button type="submit"
                                class="inline-flex h-[42px] items-center gap-2 rounded-xl bg-[#e66a4a] px-6 text-sm font-medium text-white transition hover:bg-[#ff7b5c]">
                                <span class="material-symbols-outlined text-[18px]">save</span>
                                {{ __('tickets.save_ticket') }}
                            </button>
                            <button type="button" @click="showTicketModal = false"
                                class="inline-flex h-[42px] items-center gap-2 rounded-xl border border-white/10 bg-[#262626] px-6 text-sm text-neutral-300 transition hover:bg-[#2f2f2f] hover:text-white">
                                {{ __('tickets.cancel') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
