@php
    $menuGroups = [
        'main' => [
            ['route' => 'dashboard', 'params' => [], 'icon' => 'dashboard', 'label' => 'Dashboard'],
            ['route' => 'gamas.index', 'params' => [], 'icon' => 'sensors', 'label' => 'GAMAS', 'active_pattern' => 'gamas.*'],
            ['route' => 'cids.index', 'params' => [], 'icon' => 'lan', 'label' => 'Master CID', 'active_pattern' => 'cids.*'],
            ['route' => 'tickets.index', 'params' => ['status' => 'open'], 'icon' => 'notifications_active', 'label' => 'Opened Ticket'],
            ['route' => 'tickets.index', 'params' => ['status' => 'pending'], 'icon' => 'hourglass_top', 'label' => 'Pending Ticket'],
            ['route' => 'tickets.index', 'params' => ['status' => 'closed'], 'icon' => 'task_alt', 'label' => 'Closed Ticket'],
        ],
        'reports' => [
            ['route' => 'sla.monthly', 'params' => [], 'icon' => 'bar_chart', 'label' => 'SLA Bulanan'],
            ['route' => 'sla.restitution', 'params' => [], 'icon' => 'savings', 'label' => 'Restitusi'],
            ['route' => 'activity-logs.index', 'params' => [], 'icon' => 'history', 'label' => 'Log Aktivitas'],
        ],
    ];

    if (! function_exists('slamNavActive')) {
        function slamNavActive($route, $params = [], $activePattern = null) {
            $pattern = $activePattern ?? ($route . '*');
            
            // Special handling for ticket detail pages to keep sidebar active
            if (request()->routeIs('tickets.show') || request()->routeIs('tickets.edit')) {
                $ticket = request()->route('ticket');
                if ($ticket && isset($params['status']) && $ticket->status === $params['status']) {
                    return true;
                }
            }

            if (! request()->routeIs($pattern)) {
                return false;
            }

            foreach ($params as $key => $value) {
                if (request($key) !== $value) {
                    return false;
                }
            }

            return true;
        }
    }
@endphp

<aside class="fixed inset-y-0 left-0 z-40 flex w-[280px] flex-col border-r border-white/5 bg-[#232222]">
    <div class="flex items-center gap-2 px-5 pt-4">
        <span class="h-3 w-3 rounded-full bg-[#ff6b63]/90"></span>
        <span class="h-3 w-3 rounded-full bg-[#ffcf5a]/90"></span>
        <span class="h-3 w-3 rounded-full bg-[#33d17a]/90"></span>
    </div>

    <div class="px-5 py-6">
        <div class="flex items-center gap-3 px-4 py-4">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#e66a4a]/12 ring-1 ring-[#e66a4a]/20">
                <span class="material-symbols-outlined text-[22px] text-[#f07a5f]">hub</span>
            </div>
            <div>
                <h1 class="text-[15px] font-semibold leading-5 text-white">SLA Monitor</h1>
                <p class="mt-1 text-[11px] text-neutral-400">v2.0</p>
            </div>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto px-4 pb-4">
        @foreach ($menuGroups as $groupLabel => $items)
            <div class="mb-8">
                <p class="px-2 pb-3 text-[10px] font-semibold uppercase tracking-[0.26em] text-neutral-400/80">
                    {{ strtoupper($groupLabel) }}
                </p>
                <div class="space-y-1.5">
                    @foreach ($items as $item)
                        @php $active = slamNavActive($item['route'], $item['params'] ?? [], $item['active_pattern'] ?? null); @endphp
                        <a href="{{ route($item['route'], $item['params']) }}"
                           class="flex items-center gap-3 rounded-xl px-3.5 py-3 text-[14px] transition duration-200 {{ $active ? 'bg-[#e66a4a]/10 text-[#e66a4a] ring-1 ring-[#e66a4a]/20' : 'text-neutral-400 hover:bg-white/4 hover:text-neutral-100' }}">
                            <span class="material-symbols-outlined text-[20px] {{ $active ? 'text-[#e66a4a]' : 'text-neutral-500' }}">{{ $item['icon'] }}</span>
                            <span class="font-medium leading-none">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>
</aside>
