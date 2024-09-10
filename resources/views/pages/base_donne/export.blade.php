@extends('layouts.master')
@section('title')
    Exportation
@endsection
@section('css')
    <!--datatable css-->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <!--datatable responsive css-->
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet"
        type="text/css" />
    <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Donnees
        @endslot
        @slot('title')
            Exportation
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card" id="orderList">
                <div class="card-header border-0">
                    <div class="row align-items-center gy-3">

                        <div class="row">
                            <form action="{{ route('export.data') }}" method="POST">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-xxl-2 col-sm-4">
                                        <label class="form-label" for="">Saison</label>
                                        <select class="form-control" id="saison" name="saison">
                                            <option value="">Select Season</option>
                                            @foreach($saisons as $saison)
                                                <option value="{{ $saison->id }}">{{ $saison->annee }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <x-input :attrs="[
                                        'col' => 'col-xxl-2 col-sm-4',
                                        'label' => 'Les programmes',
                                        'id' => 'programme',
                                        'class' => 'form-control form_element',
                                        'name' => 'programme',
                                        'type' => 'select',
                                        'required' => 'required',
                                        'options' => [
                                            '1' => 'Programme Saisonnier',
                                            '2' => 'Programme Semaine',
                                            '3' => 'Programme par créneau horaire',
                                        ],
                                        'placeholder' => 'Sélectionnez un programme...',
                                    ]" />
                                </div>
                                <x-input :attrs="[
                                    'col' => 'col-xxl-2 col-sm-4',
                                    'label' => '',
                                    'id' => 'export',
                                    'class' => 'form-control form_element btn btn-secondary',
                                    'name' => 'export',
                                    'type' => 'submit',
                                    'value' => 'Export',
                                ]" />

                            </form>
                        </div>



                    </div>
                </div>

            </div>

        </div>
        <!--end col-->
    </div>
    <!--end row-->
@endsection
{{-- @section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script>
        let Table = useDatatable({
            id: 'saison',
            url: "{{ route('import.data') }}",
            cols: ["annee", "action"]
        })
    </script>
@endsection --}}
