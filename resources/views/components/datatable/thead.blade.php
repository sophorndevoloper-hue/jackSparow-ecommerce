@aware(['dataTable' => null])

@props([
    'dataTable' => null,
    'columns' => null,
    'class' => 'table-light',
])

@php
    $resolvedColumns = $columns;
    if (! $resolvedColumns && $dataTable && method_exists($dataTable, 'getColumns')) {
        $resolvedColumns = $dataTable->getColumns();
    }
@endphp

@if($resolvedColumns)
    <thead class="{{ $class }}">
        <tr>
            @foreach($resolvedColumns as $column)
                @php
                    $isColObject = is_object($column);
                    $title = $isColObject ? ($column->title ?? '') : ($column['title'] ?? '');
                    $orderable = $isColObject ? ($column->orderable ?? true) : ($column['orderable'] ?? true);
                    $className = $isColObject ? ($column->className ?? '') : ($column['className'] ?? ($column['class'] ?? ''));
                    $width = $isColObject ? ($column->width ?? null) : ($column['width'] ?? null);
                    $attributes = $isColObject ? ($column->attributes ?? []) : ($column['attributes'] ?? []);

                    $classes = array_filter([
                        $className,
                        ! $orderable ? 'no-sort' : null,
                    ]);
                    $classStr = trim(implode(' ', array_unique(explode(' ', implode(' ', $classes)))));

                    $styles = [];
                    if (! empty($width)) {
                        $styles[] = 'width: ' . (is_numeric($width) ? $width . 'px' : $width) . ';';
                    }
                    if (! empty($attributes['style'])) {
                        $styles[] = rtrim($attributes['style'], ';') . ';';
                        unset($attributes['style']);
                    }
                    $styleStr = trim(implode(' ', $styles));
                @endphp
                @php
                    $attrParts = [];
                    if (! empty($classStr)) {
                        $attrParts[] = 'class="' . e($classStr) . '"';
                    }
                    if (! empty($styleStr)) {
                        $attrParts[] = 'style="' . e($styleStr) . '"';
                    }
                    foreach ($attributes as $attrKey => $attrVal) {
                        $attrParts[] = e($attrKey) . '="' . e($attrVal) . '"';
                    }
                    $attrOutput = count($attrParts) ? ' ' . implode(' ', $attrParts) : '';
                @endphp
                <th{!! $attrOutput !!}>{!! $title !!}</th>
            @endforeach
        </tr>
    </thead>
@endif
