<x-app-layout>
    <x-slot name="header">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
            <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">history</span>
        </div>
        <div>
            <h2 class="text-[22px] font-semibold tracking-tight text-white">Log Aktivitas</h2>
            <p class="mt-1 text-sm text-neutral-500">Riwayat aksi yang dilakukan user di aplikasi.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="slam-panel p-6">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
                    <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">filter_alt</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-white">Filter & Pencarian</p>
                    <p class="text-xs text-neutral-500">Cari berdasarkan user, aksi, deskripsi, atau modul</p>
                </div>
            </div>

            <form method="GET" action="{{ route('activity-logs.index') }}" class="mt-5 grid gap-3 lg:grid-cols-[1fr_160px_110px] lg:items-end">
                <div class="flex flex-col gap-1.5">
                    <x-input-label for="search" value="Pencarian" class="text-xs font-medium uppercase tracking-[0.15em] text-neutral-400" />
                    <div class="relative">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-neutral-500">search</span>
                        <x-text-input id="search" name="search" type="search" class="block w-full pl-10" placeholder="Cari user, aksi, deskripsi…" :value="$search" />
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <x-input-label for="action" value="Aksi" class="text-xs font-medium uppercase tracking-[0.15em] text-neutral-400" />
                    <select id="action" name="action" class="block w-full rounded-xl border border-white/10 bg-[#262626] px-4 py-2.5 text-sm text-white transition focus:border-[#e66a4a] focus:outline-none focus:ring-2 focus:ring-[#e66a4a]/20">
                        <option value="">Semua</option>
                        @foreach ($actions as $item)
                            <option value="{{ $item }}" @selected($action === $item)>{{ ucfirst($item) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1.5">
                    <x-input-label for="per_page" value="Tampil" class="text-xs font-medium uppercase tracking-[0.15em] text-neutral-400" />
                    <select id="per_page" name="per_page" class="block w-full rounded-xl border border-white/10 bg-[#262626] px-4 py-2.5 text-sm text-white transition focus:border-[#e66a4a] focus:outline-none focus:ring-2 focus:ring-[#e66a4a]/20">
                        @foreach ([10, 25, 50] as $option)
                            <option value="{{ $option }}" @selected((int) $perPage === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2 border-t border-white/5 pt-4 lg:col-span-3">
                    <button type="submit" class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-[#e66a4a] px-4 text-sm font-medium text-white shadow-sm shadow-[#e66a4a]/20 transition hover:bg-[#ff7b5c] active:scale-[0.97]">
                        <span class="material-symbols-outlined text-[16px]">tune</span>
                        Terapkan Filter
                    </button>
                    @if (request()->has('search') || request()->has('action') || request()->has('per_page'))
                        <a href="{{ route('activity-logs.index') }}" class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-white/10 bg-transparent px-4 text-sm text-neutral-400 transition hover:border-white/20 hover:text-white active:scale-[0.97]">
                            <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                            Reset
                        </a>
                    @endif
                    <span class="ml-auto text-xs text-neutral-500">{{ $logs->total() }} log ditemukan</span>
                </div>
            </form>
        </div>

        <div class="slam-panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-white/5 bg-[#2a2a2a] text-[11px] uppercase tracking-[0.18em] text-neutral-500">
                        <tr>
                            <th class="px-6 py-4 font-medium">Waktu</th>
                            <th class="px-6 py-4 font-medium">User</th>
                            <th class="px-6 py-4 font-medium">Aksi</th>
                            <th class="px-6 py-4 font-medium">Deskripsi</th>
                            <th class="px-6 py-4 font-medium">Modul</th>
                            <th class="px-6 py-4 font-medium">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($logs as $log)
                            <tr class="transition duration-150 hover:bg-white/[0.03]">
                                <td class="whitespace-nowrap px-6 py-4 text-neutral-400">{{ $log->created_at ? \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i:s') : '-' }}</td>
                                <td class="px-6 py-4 text-neutral-200">{{ $log->user?->name ?? 'System' }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full bg-[#e66a4a]/10 px-3 py-1 text-xs text-[#e66a4a]">{{ ucfirst($log->action) }}</span>
                                </td>
                                <td class="px-6 py-4 text-neutral-200">{{ $log->description }}</td>
                                <td class="px-6 py-4 text-neutral-400">{{ class_basename($log->model_type) ?: '-' }}{{ $log->model_id ? ' #'.$log->model_id : '' }}</td>
                                <td class="px-6 py-4 text-neutral-500">{{ $log->ip_address ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-white/5">
                                        <span class="material-symbols-outlined text-2xl text-neutral-500">history</span>
                                    </div>
                                    <p class="text-sm text-neutral-500">Belum ada log aktivitas.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="border-t border-white/5 pt-4">
            {{ $logs->links() }}
        </div>
    </div>
</x-app-layout>
