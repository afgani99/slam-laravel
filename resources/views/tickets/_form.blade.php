@csrf

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <x-input-label for="cid_id" value="CID" />
        <select id="cid_id" name="cid_id" class="mt-1 block w-full rounded-lg border-neutral-700 bg-neutral-900 text-neutral-100 shadow-sm focus:border-orange-500 focus:ring-orange-500/30" required>
            <option value="" class="bg-neutral-900">Pilih CID</option>
            @foreach ($cids as $cid)
                <option value="{{ $cid->id }}" class="bg-neutral-900" @selected((int) old('cid_id', $ticket->cid_id) === $cid->id)>
                    {{ $cid->cid }} — {{ $cid->customer_name }} / {{ $cid->service }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('cid_id')" />
    </div>

    <div>
        <x-input-label for="vendor_ticket_number" value="Ticket ID Vendor" />
        <x-text-input id="vendor_ticket_number" name="vendor_ticket_number" type="text" class="mt-1 block w-full" :value="old('vendor_ticket_number', $ticket->vendor_ticket_number)" />
        <x-input-error class="mt-2" :messages="$errors->get('vendor_ticket_number')" />
    </div>

    <div>
        <x-input-label for="case_type" value="Kasus" />
        <select id="case_type" name="case_type" class="mt-1 block w-full rounded-lg border-neutral-700 bg-neutral-900 text-neutral-100 shadow-sm focus:border-orange-500 focus:ring-orange-500/30" required>
            <option value="" class="bg-neutral-900">Pilih kasus</option>
            @foreach ($caseTypes as $caseType)
                <option value="{{ $caseType }}" class="bg-neutral-900" @selected(old('case_type', $ticket->case_type) === $caseType)>{{ $caseType }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('case_type')" />
    </div>

    <div>
        <x-input-label for="started_at" value="Waktu Mulai" />
        <x-text-input id="started_at" name="started_at" type="datetime-local" class="mt-1 block w-full" :value="old('started_at', $ticket->started_at?->format('Y-m-d\TH:i'))" required />
        <x-input-error class="mt-2" :messages="$errors->get('started_at')" />
    </div>

    @if ($showCompletionFields ?? false)
        <div>
            <x-input-label for="finished_at" value="Waktu Selesai" />
            <x-text-input id="finished_at" name="finished_at" type="datetime-local" class="mt-1 block w-full" :value="old('finished_at', $ticket->finished_at?->format('Y-m-d\TH:i'))" />
            <x-input-error class="mt-2" :messages="$errors->get('finished_at')" />
        </div>

        <div class="md:col-span-2">
            <x-input-label for="rfo_action" value="RFO / Action" />
            <textarea id="rfo_action" name="rfo_action" rows="4" class="mt-1 block w-full rounded-lg border-neutral-700 bg-neutral-900 text-neutral-100 placeholder-neutral-500 focus:border-orange-500 focus:ring-orange-500/30">{{ old('rfo_action', $ticket->rfo_action) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('rfo_action')" />
        </div>
    @endif
</div>

<div class="mt-6 flex items-center gap-3">
    <x-primary-button>{{ $submitLabel }}</x-primary-button>
    <a href="{{ $cancelUrl }}" class="inline-flex items-center gap-2 rounded-lg border border-neutral-700 bg-neutral-800 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-neutral-300 transition-colors hover:bg-neutral-700/50">
        Batal
    </a>
</div>
