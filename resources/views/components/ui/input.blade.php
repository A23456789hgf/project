@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => '',
    'required' => false,
    'error' => null,
])

<div class="ds-form-group">
    @if($label)
        <label for="{{ $name }}" class="ds-label">
            {{ $label }}
            @if($required) <span class="text-danger">*</span> @endif
        </label>
    @endif
    <input 
        type="{{ $type }}" 
        name="{{ $name }}" 
        id="{{ $name }}" 
        value="{{ old($name, $value) }}" 
        placeholder="{{ $placeholder }}" 
        {{ $required ? 'required' : '' }} 
        {{ $attributes->merge(['class' => 'ds-input' . ($errors->has($name) ? ' is-invalid' : '')]) }}
    />
    @error($name)
        <div class="invalid-feedback d-block text-danger mt-1 small">{{ $message }}</div>
    @enderror
</div>
