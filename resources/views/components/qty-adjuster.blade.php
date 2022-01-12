<div @class([
    'input-group has-validation',
    'input-group-lg' => $size === 'lg',
    'input-group-sm' => $size === 'sm'
])>
    <button class="btn btn-danger -minus" type="button" 
        @if (isset($minus)) data-bs-toggle="tooltip" title="{{ $minus }}" @endif>
        <i class="bi bi-dash-lg"></i>
    </button>
    <input type="number" name="{{ $name }}" value="0" 
        class="form-control text-center @error($name) is-invalid @enderror"
        @if (isset($min)) min="{{ $min }}" @endif 
        @if (isset($max)) max="{{ $max }}" @endif>
    <button class="btn btn-success -plus" type="button" 
        @if (isset($plus)) data-bs-toggle="tooltip" title="{{ $plus }}" @endif>
        <i class="bi bi-plus-lg"></i>
    </button>
    <div class="invalid-feedback text-center">
        @error($name)
            {{ $message }}
        @enderror
    </div>
</div>