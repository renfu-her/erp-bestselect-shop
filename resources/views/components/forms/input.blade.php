<div class="form-group row">
    {{ $slot }}
    <label for="{{ $name }}" class="col-sm-3 col-md-2 col-form-label text-md-right">{{ $title }}</label>
    <div class="input-group col-sm-9">
        @if (isset($prepend))
            <div class="input-group-prepend">
                {{ $prepend }}
            </div>
        @endif
        <input type="{{ $type }}" class="form-control" id="{{ $name }}" name="{{ $name }}">
        @if (isset($append))
            <div class="input-group-append">
                {{ $append }}
            </div>
        @endif
    </div>
    @if (isset($help))
        <small id="{{ $name }}Help" class="offset-sm-3 offset-md-2 form-text text-muted pl-3">
            We'll never share your email with anyone else.
        </small>
    @endif
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
