@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between gap-4">
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-neutral-500">
                    Showing {{ $paginator->firstItem() ?? 0 }} - {{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} results
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex rounded-xl overflow-hidden border border-white/5 bg-[#262626] shadow-sm">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span class="inline-flex items-center px-3 py-2 text-sm text-neutral-600 cursor-not-allowed border-r border-white/5 bg-[#262626]">
                            <span class="material-symbols-outlined text-[16px]">chevron_left</span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-3 py-2 text-sm text-neutral-300 transition hover:bg-[#2f2f2f] hover:text-white border-r border-white/5">
                            <span class="material-symbols-outlined text-[16px]">chevron_left</span>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="inline-flex items-center px-4 py-2 text-sm text-neutral-500 border-r border-white/5 bg-[#262626]">{{ $element }}</span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-[#e66a4a] border-r border-white/5">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="inline-flex items-center px-4 py-2 text-sm text-neutral-300 transition hover:bg-[#2f2f2f] hover:text-white border-r border-white/5">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-3 py-2 text-sm text-neutral-300 transition hover:bg-[#2f2f2f] hover:text-white">
                            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                        </a>
                    @else
                        <span class="inline-flex items-center px-3 py-2 text-sm text-neutral-600 cursor-not-allowed">
                            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                        </span>
                    @endif
                </span>
            </div>
        </div>

        <div class="flex w-full flex-col gap-3 sm:hidden">
            <p class="text-xs uppercase tracking-[0.16em] text-neutral-500">
                Showing {{ $paginator->firstItem() ?? 0 }} - {{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} results
            </p>
            <div class="flex items-center justify-between rounded-xl border border-white/5 bg-[#262626] p-1">
                @if ($paginator->onFirstPage())
                    <span class="inline-flex items-center px-3 py-2 text-sm text-neutral-600 cursor-not-allowed">
                        <span class="material-symbols-outlined text-[16px]">chevron_left</span>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-3 py-2 text-sm text-neutral-300 transition hover:text-white">
                        <span class="material-symbols-outlined text-[16px]">chevron_left</span>
                    </a>
                @endif

                <span class="text-sm text-neutral-400">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-3 py-2 text-sm text-neutral-300 transition hover:text-white">
                        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    </a>
                @else
                    <span class="inline-flex items-center px-3 py-2 text-sm text-neutral-600 cursor-not-allowed">
                        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
