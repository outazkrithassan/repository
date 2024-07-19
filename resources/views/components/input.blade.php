@props(['attrs'])

<div class="{{ $attrs['col'] }} mb-2">
    <label for="{{ $attrs['id'] }}" class="form-label text-capitalize">{{ $attrs['label'] }}</label>
    <input
        @foreach ($attrs as $key => $value)
            @if ($key != 'label' && $key != 'col')
               @php  
                    echo $key. '=' .'"'.$value.'"'
               @endphp
            @endif @endforeach>
    <div id="error_{{ $attrs['id'] }}" class="mt-2 text-danger fs-12"></div>
</div>
