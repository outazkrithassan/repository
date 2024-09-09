@props(['attrs'])

<div class="{{ $attrs['col'] ?? 'col-md-12' }} mb-2">
    <label for="{{ $attrs['id'] }}" class="form-label text-capitalize">{{ $attrs['label'] }}</label>

    @if ($attrs['type'] === 'select')
        <select id="{{ $attrs['id'] }}" name="{{ $attrs['name'] }}" class="{{ $attrs['class'] }}" {{ $attrs['required'] ? 'required' : '' }}>
            <option value="">{{ $attrs['placeholder'] }}</option>
            @foreach ($attrs['options'] as $key => $value)
                <option value="{{ $key }}">{{ $value }}</option>
            @endforeach
        </select>
    @elseif ($attrs['type'] === 'button')
        <button id="{{ $attrs['id'] }}" name="{{ $attrs['name'] }}" class="{{ $attrs['class'] }}" {{ $attrs['required'] ? 'required' : '' }}>
            {{ $attrs['value'] }}
        </button>
    @else
        <input
            @foreach ($attrs as $key => $value)
                @if (!is_array($value) && $key != 'label' && $key != 'col' && $key != 'options' && $key != 'placeholder')
                    {{ $key }}="{{ $value }}"
                @endif
            @endforeach
            placeholder="{{ $attrs['placeholder'] ?? '' }}"
        >
    @endif

    <div id="error_{{ $attrs['id'] }}" class="mt-2 text-danger fs-12"></div>
</div>
