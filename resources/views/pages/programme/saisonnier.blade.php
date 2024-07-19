@extends('layouts.master')
@section('title')
    saisonnier
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
            programme
        @endslot
        @slot('title')
            saisonnier
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card" id="orderList">
                <div class="card-header border-0">
                    <div class="row align-items-center gy-3">
                        <div class="col-sm">
                            <h5 class="card-title mb-0">Programme de saison</h5>
                        </div>
                    </div>
                </div>

                <div class="card-body border border-dashed border-end-0 border-start-0">
                    <div class="row g-3">
                        <div class="col-xxl-2 col-sm-4">
                            <label class="form-label" for="">Saison</label>
                            <select class="form-control" name="choices-single-default" id="saison">
                                <option value="">2024</option>
                            </select>
                        </div>
                        <div class="col-xxl-2 col-sm-4">
                            <label class="form-label" for="">Mouvement</label>
                            <select class="form-control filter_data" table_id="vols" name="mouvement" id="mouvement">
                                <option value="">Arrivée/Depart</option>
                                <option value="-1">Arrivée</option>
                                <option value="1">Depart</option>
                            </select>
                        </div>
                        <div class="col-xxl-2 col-sm-4">
                            <label class="form-label" for="">Mois</label>
                            <select class="form-control" name="choices-single-default" id="idPayment">
                                <option value="">Tous</option>
                                <option value="1">Janvier</option>
                                <option value="2">Février</option>
                                <option value="3">Mars</option>
                                <option value="4">Avril</option>
                                <option value="5">Mai</option>
                                <option value="6">Juin</option>
                                <option value="7">Juillet</option>
                                <option value="8">Août</option>
                                <option value="9">Septembre</option>
                                <option value="10">Octobre</option>
                                <option value="11">Novembre</option>
                                <option value="12">Décembre</option>
                            </select>
                        </div>
                        <div class="col-xxl-2 col-sm-4">
                            <label class="form-label" for="">Jour</label>
                            <select class="form-control" name="choices-single-default" id="idPayment">
                                <option value="">Tous</option>
                                <option value="1">Lundi</option>
                                <option value="2">Mardi</option>
                                <option value="3">Mercredi</option>
                                <option value="4">Jeudi</option>
                                <option value="5">Vendredi</option>
                                <option value="6">Samedi</option>
                                <option value="7">Dimanche</option>
                            </select>
                        </div>
                        <div class="col-xxl-2 col-sm-4">
                            <label class="form-label" for="">DU</label>
                            <input type="date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-xxl-2 col-sm-4">
                            <label class="form-label" for="">Au</label>
                            <input type="date" class="form-control" value="">
                        </div>

                    </div>
                    <!--end row-->
                </div>

                <div class="card-body pt-0 mt-3">
                    <div class="row">
                        <div class="col-12">
                            @php
                                $thead = ['date vol', 'numero','Type APP','Capacite', 'arrive', 'heure arrive', 'depart', 'heure depart'];
                            @endphp
                            <x-table id='vols' :thead="$thead"></x-table>
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
            id: 'vols',
            url: "{{ route('vol.data_saisonnier') }}",
            cols: ["date_vol", "numero","equipement","capacite", "arrivee", "heure_arrive", "depart",
                "heure_depart",
            ],
            data : ["mouvement"]
        })
    </script>
    {{-- <script>
        $(document).ready(function() {
            let Table = useDatatable({
                id: 'vols',
                url: "{{ route('vol.data_saisonnier') }}",
                cols: ["date_vol", "numero", "equipement", "capacite", "arrivee", "heure_arrive", "depart", "heure_depart"],
                data: ["mouvement"]
            });

            // Reload DataTable when select input changes
            $('#mouvement').change(function() {
                let selectedMouvement = $(this).val();
                Table.ajax.url("{{ route('vol.data_saisonnier') }}" + "?mouvement=" + selectedMouvement).load();
            });
        });
    </script> --}}
@endsection
