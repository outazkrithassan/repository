@props(['attrs'])

<div class="{{ $attrs['col'] }} mb-2 ">
    <label for="{{ $attrs['id'] }}" class="form-label text-capitalize">{{ $attrs['label'] }}</label>
    <select
        @foreach ($attrs as $key => $value)
            @if ($key !== 'label' && $key !== 'col' && $key !== 'options' && $key != 'selected')
               @php  
                    echo $key. '=' .'"'.$value.'"'
               @endphp
            @endif @endforeach>
        @foreach ($attrs['options'] as $option)
            @php
                $selcted = $option['id'] == $attrs['selected'] ? 'selected' : '';
            @endphp
            <option value="{{ $option['id'] }}" {{ $selcted }}>{{ $option['title'] }}
            </option>
        @endforeach
    </select>
    <div id="error_{{ $attrs['id'] }}" class="mt-2 text-danger fs-12"></div>
</div>
