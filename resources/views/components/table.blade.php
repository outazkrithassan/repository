@props(['id', 'thead'])

<table id="{{ $id }}" class="table text-center  table-bordered nowrap dt-responsive table-striped align-middle">
    <thead class="bg-light">
        @foreach ($thead as $th)
            <th> {{ $th }} </th>
        @endforeach
    </thead>

</table>
