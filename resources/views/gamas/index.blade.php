<x-app-layout>
    <x-slot name="header">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
            <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">sensors</span>
        </div>
        <div>
            <h2 class="text-[22px] font-semibold tracking-tight text-white">GAMAS</h2>
            <p class="mt-1 text-sm text-neutral-500">Gangguan Massal — manage bulk incidents.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="flex flex-col gap-4 rounded-2xl border border-white/5 bg-[#161616] p-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <p class="text-sm text-neutral-400">Total <span class="font-semibold text-white">{{ $gamasList->total() }}</span> GAMAS</p>
                <form action="{{ route('gamas.index') }}" method="GET" class="flex gap-2">
                    <x-text-input name="search" value="{{ $search }}" placeholder="Cari Gamas..." class="h-9 w-48 text-sm" />
                    <select name="status" class="h-9 rounded-lg border-neutral-700 bg-neutral-900 text-sm text-neutral-300">
                        <option value="">Semua Status</option>
                        @foreach (\App\Models\Gamas::STATUSES as $statusOption)
                            <option value="{{ $statusOption }}" {{ $status == $statusOption ? 'selected' : '' }}>
                                {{ ucfirst($statusOption) }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-lg bg-white/10 px-3 text-sm text-white hover:bg-white/20">Filter</button>
                    @if($search || $status)
                        <a href="{{ route('gamas.index') }}" class="rounded-lg bg-neutral-800 px-3 py-2 text-sm text-neutral-400 hover:text-white">Reset</a>
                    @endif
                </form>
            </div>
            <a href="{{ route('gamas.create') }}" class="slam-primary-btn">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Buat GAMAS
            </a>
        </div>

        <div class="slam-card p-6">
            <div class="overflow-hidden rounded-2xl border border-white/5 bg-[#151515]">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-white/5 text-[11px] uppercase tracking-[0.18em] text-neutral-500">
                        <tr>
                            <th class="px-6 py-4">Gamas #</th>
                            <th class="px-6 py-4">Case Type</th>
                            <th class="px-6 py-4">Mulai</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Tiket</th>
                            <th class="px-6 py-4">Selesai</th>
                            <th class="px-6 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($gamasList as $gamas)
                            <tr class="transition duration-200 hover:bg-white/5">
                                <td class="px-6 py-4">
                                    <a href="{{ route('gamas.show', $gamas) }}" class="font-medium text-orange-400">{{ $gamas->gamas_number }}</a>
                                    @if ($gamas->vendor_ticket_number)
                                        <p class="text-[11px] text-neutral-500">{{ $gamas->vendor_ticket_number }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-neutral-300">{{ $gamas->case_type }}</td>
                                <td class="px-6 py-4 text-neutral-400">{{ $gamas->started_at?->format('d M H:i') }}</td>
                                <td class="px-6 py-4">@include('tickets._status-badge', ['status' => $gamas->status])</td>
                                <td class="px-6 py-4">
                                    <span class="text-white">{{ $gamas->tickets_count }}</span>
                                    <span class="text-neutral-500">tiket</span>
                                </td>
                                <td class="px-6 py-4 text-neutral-400">{{ $gamas->finished_at?->format('d M H:i') ?: '-' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('gamas.destroy', $gamas) }}" method="POST" 
                                          onsubmit="return confirm('Hapus GAMAS ini dan semua tiket terkait?')" 
                                          class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-neutral-500 hover:text-red-500 transition duration-200" title="Hapus">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-12 text-center text-neutral-500">Belum ada data GAMAS.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-6">{{ $gamasList->links() }}</div>
        </div>
    </div>
</x-app-layout>
