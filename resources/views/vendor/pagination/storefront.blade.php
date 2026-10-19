@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col sm:flex-row items-center justify-between gap-4 py-4 px-1">
        
        <!-- Results Counter Summary -->
        <div class="text-xs text-gray-500 font-medium order-2 sm:order-1 text-center sm:text-left">
            Showing 
            <span class="font-bold text-gray-900">{{ $paginator->firstItem() ?? 0 }}</span>
            to 
            <span class="font-bold text-gray-900">{{ $paginator->lastItem() ?? 0 }}</span>
            of 
            <span class="font-bold text-gray-900">{{ $paginator->total() }}</span>
            results
        </div>

        <!-- Pagination Controls -->
        <div class="inline-flex items-center gap-1.5 order-1 sm:order-2">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs font-bold text-gray-400 bg-gray-100 border border-gray-200 rounded-xl cursor-not-allowed select-none opacity-60">
                    <i class="bi bi-chevron-left text-xs"></i>
                    <span class="hidden sm:inline">Previous</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs font-bold text-gray-700 bg-white hover:bg-blue-50 hover:text-blue-600 hover:border-blue-300 border border-gray-200 rounded-xl transition shadow-xs active:scale-95" aria-label="Previous Page">
                    <i class="bi bi-chevron-left text-xs"></i>
                    <span class="hidden sm:inline">Previous</span>
                </a>
            @endif

            {{-- Desktop Pagination Elements --}}
            <div class="hidden sm:inline-flex items-center gap-1">
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="w-9 h-9 flex items-center justify-center text-xs font-bold text-gray-400 select-none">
                            {{ $element }}
                        </span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="w-9 h-9 flex items-center justify-center text-xs font-extrabold text-white bg-blue-600 rounded-xl shadow-md shadow-blue-500/25 select-none" aria-current="page">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" class="w-9 h-9 flex items-center justify-center text-xs font-bold text-gray-700 bg-white hover:bg-blue-50 hover:text-blue-600 hover:border-blue-300 border border-gray-200 rounded-xl transition shadow-xs active:scale-95" aria-label="Page {{ $page }}">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Mobile Current Page Indicator --}}
            <span class="sm:hidden px-3.5 py-2 text-xs font-extrabold text-gray-700 bg-white border border-gray-200 rounded-xl shadow-xs">
                Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
            </span>

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs font-bold text-gray-700 bg-white hover:bg-blue-50 hover:text-blue-600 hover:border-blue-300 border border-gray-200 rounded-xl transition shadow-xs active:scale-95" aria-label="Next Page">
                    <span class="hidden sm:inline">Next</span>
                    <i class="bi bi-chevron-right text-xs"></i>
                </a>
            @else
                <span class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs font-bold text-gray-400 bg-gray-100 border border-gray-200 rounded-xl cursor-not-allowed select-none opacity-60">
                    <span class="hidden sm:inline">Next</span>
                    <i class="bi bi-chevron-right text-xs"></i>
                </span>
            @endif
        </div>

    </nav>
@endif

