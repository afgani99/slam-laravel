<x-app-layout>
    <x-slot name="header">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
            <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">sensors</span>
        </div>
        <div>
            <h2 class="text-[22px] font-semibold tracking-tight text-white">Edit GAMAS</h2>
            <p class="mt-1 hidden text-sm text-neutral-500 sm:block">{{ $gamas->gamas_number }}</p>
        </div>
    </x-slot>

    <div class="slam-card p-6">
        <form method="POST" action="{{ route('gamas.update', $gamas) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <x-input-label for="edit_vendor_ticket_number" value="Ticket ID Vendor" />
                    <x-text-input id="edit_vendor_ticket_number" name="vendor_ticket_number" type="text" class="mt-1 block w-full" :value="old('vendor_ticket_number', $gamas->vendor_ticket_number)" />
                    <x-input-error class="mt-2" :messages="$errors->get('vendor_ticket_number')" />
                </div>
                <div>
                    <x-input-label for="edit_case_type" value="Kasus" />
                    <div class="relative mt-1">
                        <select id="edit_case_type" name="case_type" class="block h-[42px] w-full appearance-none rounded-lg border border-neutral-700 bg-neutral-900 pr-10 text-neutral-100 shadow-sm focus:border-orange-500 focus:ring-orange-500/30" required>
                            @foreach ($caseTypes as $caseType)
                                <option value="{{ $caseType }}" class="bg-neutral-900" @selected(old('case_type', $gamas->case_type) === $caseType)>{{ $caseType }}</option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-neutral-500"><span class="material-symbols-outlined text-[18px]">expand_more</span></span>
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('case_type')" />
                </div>
                <div class="md:col-span-2">
                    <x-input-label for="edit_started_at" value="Waktu Mulai" />
                    <x-text-input id="edit_started_at" name="started_at" type="datetime-local" class="mt-1 block w-full" :value="old('started_at', $gamas->started_at?->format('Y-m-d\TH:i'))" required />
                    <x-input-error class="mt-2" :messages="$errors->get('started_at')" />
                </div>
                <div class="md:col-span-2">
                    <x-input-label for="edit_rfo_action" value="RFO Action" />
                    <textarea id="edit_rfo_action" name="rfo_action" rows="4" class="mt-1 block w-full rounded-lg border border-neutral-700 bg-neutral-900 px-4 py-2 text-neutral-100 shadow-sm focus:border-orange-500 focus:ring-orange-500/30">{{ old('rfo_action', $gamas->rfo_action) }}</textarea>
                    <p class="mt-1 text-xs text-neutral-500">Hanya tiket yang belum closed yang akan diupdate.</p>
                </div>
            </div>

            <div class="flex items-center gap-3 border-t border-white/5 pt-6">
                <button type="submit" class="inline-flex h-[42px] items-center gap-2 rounded-xl bg-[#e66a4a] px-6 text-sm font-medium text-white transition hover:bg-[#ff7b5c]">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    Simpan Perubahan
                </button>
                <a href="{{ route('gamas.show', $gamas) }}" class="inline-flex h-[42px] items-center gap-2 rounded-xl border border-white/10 bg-[#262626] px-6 text-sm text-neutral-300 transition hover:bg-[#2f2f2f] hover:text-white">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
