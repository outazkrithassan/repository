@props(['attrs'])

<div class="{{ $attrs['col'] }} mb-2">
    <label for="{{ $attrs['id'] }}" class="form-label">{{ $attrs['label'] }}</label>
    <textarea
        @foreach ($attrs as $key => $value)
            @if ($key != 'label' && $key != 'col' && $key != 'value')
               @php  
                    echo $key. '=' .'"'.$value.'"'
               @endphp
            @endif @endforeach>

            {{ $attrs['value'] }}

    </textarea>
    <div id="error_{{ $attrs['id'] }}" class="mt-2 text-danger fs-12"></div>
</div>
