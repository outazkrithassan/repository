@props([
    'col' => 'col-xxl-2 col-sm-3',
    'label' => '',
    'id' => '',
    'name' => '',
    'class' => 'form-control',
    'options' => [],
    'placeholder' => '',
    'selected' => null,
    'required' => false, // Add the 'required' prop
])

<div class="{{ $col }}">
    <label class="form-label" for="{{ $id }}">{{ $label }}</label>
    <select class="{{ $class }}" name="{{ $name }}" id="{{ $id }}" {{ $required ? 'required' : '' }}>
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $value => $text)
            <option value="{{ $value }}" {{ $value == old($name, $selected) ? 'selected' : '' }}>
                {{ $text }}
            </option>
        @endforeach
    </select>
</div>
