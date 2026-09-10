@props([
    'dataTable' => null,
    'id' => null,
    'class' => 'table table-hover align-middle mb-0 w-100',
    'perPage' => 10,
    'searchPlaceholder' => 'Search records...',
    'responsive' => true,
    'autoWidth' => false,
    'paging' => false,
    'searching' => false,
    'ordering' => true,
    'info' => false,
    'order' => null,
    'processing' => false,
])

@php
    $resolvedId = $id;
    if ($dataTable && method_exists($dataTable, 'html')) {
        $htmlBuilder = $dataTable->html();
        if (! $resolvedId) {
            $resolvedId = $htmlBuilder->getTableAttribute('id') ?? ('dt-'.uniqid());
        }
    } elseif (! $resolvedId) {
        $resolvedId = 'dt-'.uniqid();
    }

    $isPaging = filter_var($paging, FILTER_VALIDATE_BOOLEAN);
    $isSearching = filter_var($searching, FILTER_VALIDATE_BOOLEAN);
    $isOrdering = filter_var($ordering, FILTER_VALIDATE_BOOLEAN);
    $isInfo = filter_var($info, FILTER_VALIDATE_BOOLEAN);
    $isResponsive = filter_var($responsive, FILTER_VALIDATE_BOOLEAN);
    $isAutoWidth = filter_var($autoWidth, FILTER_VALIDATE_BOOLEAN);
    $isProcessing = filter_var($processing, FILTER_VALIDATE_BOOLEAN);

    if ($order === null) {
        $orderJs = 'null';
    } elseif (is_array($order)) {
        $orderJs = json_encode($order);
    } else {
        $orderJs = $order;
    }
@endphp

@if($dataTable && method_exists($dataTable, 'html') && !trim($slot))
    <div class="table-responsive">
        {!! $dataTable->html()->table(array_filter([
            'id' => $resolvedId,
            'class' => $class,
        ])) !!}
    </div>

    @push('scripts')
        {!! $dataTable->html()->scripts() !!}
    @endpush
@else
    <div class="table-responsive">
        @if(trim($slot))
            {{ $slot }}
        @else
            <table id="{{ $resolvedId }}" class="{{ $class }}"></table>
        @endif
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (window.jQuery && $.fn.DataTable) {
                    var targetId = '{{ $resolvedId }}';
                    var $el = $('#' + targetId);

                    if (!$el.length) {
                        $el = $('table').filter(function() {
                            return $(this).attr('id') === targetId;
                        });
                    }

                    if ($el.length && !$.fn.DataTable.isDataTable($el)) {
                        // If table only has a single row with colspan (e.g. empty message), avoid DT column count mismatch
                        if ($el.find('tbody tr td[colspan]').length > 0 && $el.find('tbody tr').length === 1) {
                            return;
                        }

                        var initialOrder = {!! $orderJs !!};
                        if (initialOrder === null) {
                            var $defaultSortTh = $el.find('thead th[data-default-sort], thead th.default-sort');
                            if ($defaultSortTh.length) {
                                var colIdx = $defaultSortTh.first().index();
                                var colDir = $defaultSortTh.first().attr('data-default-sort') || 'desc';
                                initialOrder = [[colIdx, colDir]];
                            } else {
                                // Empty array preserves server-side latest('updated_at') / latest('created_at') query ordering without sorting column 0 ascending A-Z!
                                initialOrder = [];
                            }
                        }

                        var dtInstance = $el.DataTable({
                            processing: {{ $isProcessing ? 'true' : 'false' }},
                            order: initialOrder,
                            responsive: {{ $isResponsive ? 'true' : 'false' }},
                            autoWidth: {{ $isAutoWidth ? 'true' : 'false' }},
                            pageLength: {{ (int) $perPage }},
                            paging: {{ $isPaging ? 'true' : 'false' }},
                            searching: {{ $isSearching ? 'true' : 'false' }},
                            ordering: {{ $isOrdering ? 'true' : 'false' }},
                            info: {{ $isInfo ? 'true' : 'false' }},
                            columnDefs: [
                                { targets: 'no-sort', orderable: false }
                            ],
                            language: {
                                processing: '<div class="dt-processing-card"><div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div><span>Refreshing data...</span></div>',
                                search: "_INPUT_",
                                searchPlaceholder: "{{ addslashes($searchPlaceholder) }}",
                                lengthMenu: "Show _MENU_ entries",
                                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                                paginate: {
                                    first: '<i class="bi bi-chevron-double-left"></i>',
                                    previous: '<i class="bi bi-chevron-left"></i>',
                                    next: '<i class="bi bi-chevron-right"></i>',
                                    last: '<i class="bi bi-chevron-double-right"></i>'
                                }
                            }
                        });

                        window.dataTables = window.dataTables || {};
                        window.dataTables[targetId] = dtInstance;

                        dtInstance.on('processing.dt', function(e, settings, processing) {
                            var $proc = $('#' + targetId + '_processing');
                            if (!$proc.length) {
                                $proc = $el.closest('.dataTables_wrapper').find('.dataTables_processing');
                            }
                            if (processing) {
                                $proc.addClass('dt-processing-active');
                            } else {
                                $proc.removeClass('dt-processing-active');
                            }
                        });

                        dtInstance.on('draw.dt', function() {
                            $('[data-dt-refresh="' + targetId + '"]').prop('disabled', false).find('.refresh-icon, i').removeClass('spin');
                            var $proc = $('#' + targetId + '_processing');
                            if (!$proc.length) {
                                $proc = $el.closest('.dataTables_wrapper').find('.dataTables_processing');
                            }
                            $proc.removeClass('dt-processing-active');
                        });

                        if (!window.__dtRefreshBound) {
                            window.__dtRefreshBound = true;
                            $(document).on('click', '[data-dt-refresh]', function(e) {
                                e.preventDefault();
                                var $btn = $(this);
                                var tId = $btn.data('dt-refresh');
                                var $icon = $btn.find('.refresh-icon, i, svg').first();

                                var dt = (window.dataTables && window.dataTables[tId]) 
                                    ? window.dataTables[tId] 
                                    : ($.fn.DataTable.isDataTable('#' + tId) ? $('#' + tId).DataTable() : null);

                                if (!dt) return;

                                $btn.prop('disabled', true);
                                $icon.addClass('spin');

                                if (dt.ajax && typeof dt.ajax.reload === 'function') {
                                    dt.ajax.reload(function() {
                                        $btn.prop('disabled', false);
                                        $icon.removeClass('spin');
                                    }, false);
                                } else {
                                    setTimeout(function() {
                                        dt.draw(false);
                                        $btn.prop('disabled', false);
                                        $icon.removeClass('spin');
                                    }, 350);
                                }
                            });
                        }

                        if (!window.reloadDataTable) {
                            window.reloadDataTable = function(tId, keepPagination) {
                                if (keepPagination === undefined) keepPagination = true;
                                var dt = (window.dataTables && window.dataTables[tId]) 
                                    ? window.dataTables[tId] 
                                    : ($.fn.DataTable.isDataTable('#' + tId) ? $('#' + tId).DataTable() : null);

                                if (dt && dt.ajax && typeof dt.ajax.reload === 'function') {
                                    dt.ajax.reload(null, !keepPagination);
                                } else if (dt) {
                                    dt.draw(!keepPagination);
                                }
                            };
                        }
                    }
                }
            });
        </script>
    @endpush
@endif