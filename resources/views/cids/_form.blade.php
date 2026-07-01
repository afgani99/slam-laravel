@csrf

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <x-input-label for="cid" value="CID" />
        <x-text-input id="cid" name="cid" type="text" class="mt-1 block w-full" :value="old('cid', $cid->cid)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('cid')" />
    </div>

    <div>
        <x-input-label for="cid_is" value="CID IS" />
        <x-text-input id="cid_is" name="cid_is" type="text" class="mt-1 block w-full" :value="old('cid_is', $cid->cid_is)" />
        <x-input-error class="mt-2" :messages="$errors->get('cid_is')" />
    </div>

    <div>
        <x-input-label for="sla_percentage" value="SLA Target (%)" />
        <x-text-input id="sla_percentage" name="sla_percentage" type="number" min="0" max="100" step="0.01" class="mt-1 block w-full" :value="old('sla_percentage', $cid->sla_percentage)" required />
        <x-input-error class="mt-2" :messages="$errors->get('sla_percentage')" />
    </div>

    <div>
        <x-input-label for="vendor_name" value="Nama Vendor" />
        <x-text-input id="vendor_name" name="vendor_name" type="text" class="mt-1 block w-full" :value="old('vendor_name', $cid->vendor_name)" required />
        <x-input-error class="mt-2" :messages="$errors->get('vendor_name')" />
    </div>

    <div>
        <x-input-label for="customer_name" value="Nama Pelanggan" />
        <x-text-input id="customer_name" name="customer_name" type="text" class="mt-1 block w-full" :value="old('customer_name', $cid->customer_name)" required />
        <x-input-error class="mt-2" :messages="$errors->get('customer_name')" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="service" value="Service" />
        <x-text-input id="service" name="service" type="text" class="mt-1 block w-full" :value="old('service', $cid->service)" required />
        <x-input-error class="mt-2" :messages="$errors->get('service')" />
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <x-primary-button>{{ $submitLabel }}</x-primary-button>
    <a href="{{ $cancelUrl }}" class="inline-flex items-center gap-2 rounded-lg border border-neutral-700 bg-neutral-800 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-neutral-300 transition-colors hover:bg-neutral-700/50">
        Batal
    </a>
</div>
