@error($name) 
@php
    $isInvalid = true;
@endphp
@enderror
<div @class([
    'input-group flex-nowrap',
    'input-group-lg' => $size === 'lg',
    'input-group-sm' => $size === 'sm',
    'is-invalid' => $isInvalid ?? false
])>
    <button class="btn btn-outline-primary -minus" type="button" 
        @if (isset($minus)) data-bs-toggle="tooltip" title="{{ $minus }}" @endif>
        <i class="bi bi-dash-lg"></i>
    </button>
    <input type="number" name="{{ $name }}" value="{{ $value }}" 
        class="form-control text-center @error($name) is-invalid @enderror"
        @if (isset($min)) min="{{ $min }}" @endif 
        @if (isset($max)) max="{{ $max }}" @endif>
    <button class="btn btn-outline-primary -plus" type="button" 
        @if (isset($plus)) data-bs-toggle="tooltip" title="{{ $plus }}" @endif>
        <i class="bi bi-plus-lg"></i>
    </button>
</div>
<div class="invalid-feedback text-center">
    @error($name)
        {{ $message }}
    @enderror
</div>