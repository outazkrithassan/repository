@extends('layouts.master')
@section('title')
    Import
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
            Frére
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card" id="orderList">
                <div class="card-header border-0">
                    <div class="row align-items-center gy-3">
                        <div class="col-sm">
                            <h5 class="card-title mb-0">Vols frere </h5>
                        </div>
                        <div class="col-sm-auto">
                            <div class="d-flex gap-1 flex-wrap">
                                <button route="{{ route('frere.create') }}" type="button"
                                    class="btn btn-secondary create-btn"><i
                                        class="ri-file-download-line align-bottom me-1"></i>
                                    Nouveau</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body pt-0">

                    <div class="row">
                        <div class="col-12">
                            @php
                                $thead = ['Numero arrivee', 'Numero depart', 'action'];
                            @endphp
                            <x-table id='vol_frere' :thead="$thead"></x-table>
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
            id: 'vol_frere',
            url: "{{ route('frere.data') }}",
            cols: ["numero_arrivee", "numero_depart", "action"]
        })
    </script>
@endsection
