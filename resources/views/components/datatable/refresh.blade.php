@props([
    'target' => null,
    'text' => 'Refresh',
    'class' => 'btn btn-outline-secondary btn-sm d-flex align-items-center gap-2',
    'icon' => 'bi bi-arrow-clockwise',
])

<button type="button" 
        data-dt-refresh="{{ $target }}" 
        class="{{ $class }}" 
        title="Refresh table without page reload"
        {{ $attributes }}>
    <i class="{{ $icon }} refresh-icon"></i>
    @if(!empty($text))
        <span class="refresh-text">{{ $text }}</span>
    @endif
</button>

