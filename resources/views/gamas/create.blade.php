<x-app-layout>
    <x-slot name="header">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
            <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">sensors</span>
        </div>
        <div>
            <h2 class="text-[22px] font-semibold tracking-tight text-white">Buat GAMAS</h2>
            <p class="mt-1 hidden text-sm text-neutral-500 sm:block">Buat gangguan massal untuk beberapa CID sekaligus.</p>
        </div>
    </x-slot>

    <div class="slam-card p-6">
        <form method="POST" action="{{ route('gamas.store') }}" class="space-y-6">
            @csrf

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <x-input-label for="gamas_vendor_ticket_number" value="Ticket ID Vendor" />
                    <x-text-input id="gamas_vendor_ticket_number" name="vendor_ticket_number" type="text" class="mt-1 block w-full" :value="old('vendor_ticket_number')" />
                    <x-input-error class="mt-2" :messages="$errors->get('vendor_ticket_number')" />
                </div>
                <div>
                    <x-input-label for="gamas_case_type" value="Kasus" />
                    <div class="relative mt-1">
                        <select id="gamas_case_type" name="case_type" class="block h-[42px] w-full appearance-none rounded-lg border border-neutral-700 bg-neutral-900 pr-10 text-neutral-100 shadow-sm focus:border-orange-500 focus:ring-orange-500/30" required>
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
                    <x-input-label for="gamas_started_at" value="Waktu Mulai" />
                    <x-text-input id="gamas_started_at" name="started_at" type="datetime-local" class="mt-1 block w-full" :value="old('started_at')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('started_at')" />
                </div>
            </div>

            <div>
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

                    <div class="max-h-[400px] overflow-auto rounded-lg border border-white/5">
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

            <div class="flex items-center gap-3 border-t border-white/5 pt-6">
                <button type="submit" class="inline-flex h-[42px] items-center gap-2 rounded-xl bg-[#e66a4a] px-6 text-sm font-medium text-white transition hover:bg-[#ff7b5c]">
                    <span class="material-symbols-outlined text-[18px]">bolt</span>
                    Buat GAMAS
                </button>
                <a href="{{ route('gamas.index') }}" class="inline-flex h-[42px] items-center gap-2 rounded-xl border border-white/10 bg-[#262626] px-6 text-sm text-neutral-300 transition hover:bg-[#2f2f2f] hover:text-white">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
