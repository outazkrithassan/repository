@extends('layouts.master')
@section('title')
    Vol depart
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
            Les vols
        @endslot
        @slot('title')
            Vol depart
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card" id="orderList">
                <div class="card-header border-0">
                    <div class="row align-items-center gy-3">
                        <div class="col-sm">
                            <h5 class="card-title mb-0">Vol de depart</h5>
                        </div>
                        <div class="col-sm-auto">
                            <div class="d-flex gap-1 flex-wrap">
                                <button route="{{ route('depart.create') }}" type="button"
                                    class="btn btn-secondary create-btn"><i
                                        class="ri-file-download-line align-bottom me-1"></i>
                                    Nouveau</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body border border-dashed border-end-0 border-start-0">
                    <div class="row g-3">
                        <div class="col-xxl-2 col-sm-3">
                            <label class="form-label" for="">Saison</label>
                            <select class="form-control" name="choices-single-default" id="saison">
                                <option value="">Select Season</option>
                                @foreach($saisons as $saison)
                                    <option value="{{ $saison->id }}">{{ $saison->annee }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xxl-2 col-sm-3">
                            <label class="form-label" for="">N° depart</label>
                            <input class="form-control" type="text" name="numero_depart" id="numero_depart">

                        </div>
                        <div class="col-xxl-2 col-sm-3">
                            <label class="form-label" for="">Depart</label>
                            <input class="form-control" type="text" name="depart" id="depart">
                        </div>
                        <div class="col-xxl-2 col-sm-3">
                            <label class="form-label" for="">Heure depart</label>
                            <input class="form-control" type="text" name="Heure_depart" id="Heure_depart">
                        </div>
                        <div class="col-xxl-2 col-sm-3">
                            <label class="form-label" for="date_vol">Date Vol</label>
                            <input type="date" class="form-control" id="date_vol" name="date_vol"onchange="formatDate(this)">
                        </div>
                        <div class="col-xxl-2 col-sm-3">
                            <label class="form-label" for="">Companoie</label>
                            <select class="form-control filter_data" name="choices-single-default month" id="FilterByMois">
                                <option value="">Select Companoie</option>
                                @foreach($companies as $companie)
                                    <option value="{{ $companie->id }}">{{ $companie->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xxl-2 col-sm-3">
                            <label class="form-label" for="">Avion</label>
                            <select class="form-control" name="choices-single-default" id="day_of_week">
                                <option value="">Select Avion</option>
                                @foreach($avions as $avion)
                                    <option value="{{ $avion->id }}">{{ $avion->equipement }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <!--end row-->
                </div>

                <div class="card-body pt-0 mt-3">
                    <div class="row">
                        <div class="col-12">
                            @php
                                $thead = ['date vol', 'N° vol','Type avion','Depart', 'heure depart','action'];
                            @endphp
                            <x-table id='vol_dapart' :thead="$thead"></x-table>

                        </div>
                    </div>

                </div>
            </div>

        </div>
        <!--end col-->
    </div>
    <!--end row-->
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script>

        let Table = useDatatable({
            id: 'vol_dapart',
            url: "{{ route('vols.data_depart') }}",
            cols: ["date_vol", "numero_vol","equipement","destination","heure_depart","action"]
        })
    </script>
    {{-- script pour change format de date --}}
    <script>
        function formatDate(input) {
            let date = new Date(input.value);
            let year = date.getFullYear();
            let month = ('0' + (date.getMonth() + 1)).slice(-2); // Add leading zero
            let day = ('0' + date.getDate()).slice(-2); // Add leading zero

            let formattedDate = `${year}-${month}-${day}`;
            input.value = formattedDate; // Set the input value to the formatted date
            console.log(formattedDate); // Optional: log the formatted date
        }
        </script>
@endsection

