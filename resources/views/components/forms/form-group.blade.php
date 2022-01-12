<div class="form-group {{ $class }}@if ($border === true) border-bottom @endif">
    <label for="{{ $name }}" class="col-form-label">
        {{ $title }}
        @if($required === true)
            <span class="text-danger">*</span>
        @endif
    </label>
    {{ $slot }}
    <small class="{{ $name }}Help form-text text-muted pl-3">
        @if (isset($help))
            {{ $help }}
        @endif
    </small>
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
