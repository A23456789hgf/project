@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => 'اختر من القائمة...',
    'required' => false,
])

<div class="ds-form-group">
    @if($label)
        <label for="{{ $name }}" class="ds-label">
            {{ $label }}
            @if($required) <span class="text-danger">*</span> @endif
        </label>
    @endif
    <select name="{{ $name }}" id="{{ $name }}" {{ $required ? 'required' : '' }} {{ $attributes->merge(['class' => 'ds-select']) }}>
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach($options as $val => $txt)
            <option value="{{ $val }}" {{ (string)$val === (string)old($name, $selected) ? 'selected' : '' }}>{{ $txt }}</option>
        @endforeach
        {{ $slot }}
    </select>
</div>
