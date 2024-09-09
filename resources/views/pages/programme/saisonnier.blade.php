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
                            <select class="form-control" name="seasonId" id="seasonId">
                                <option value="">Select Season</option>
                                @foreach($saisons as $saison)
                                    <option value="{{ $saison->id }}">{{ $saison->annee }}</option>
                                @endforeach
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
                            <select class="form-control filter_data" name="month" id="month">
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
                            <select class="form-control" name="day_of_week" id="day_of_week">
                                <option value="">Tous</option>
                                <option value="Lundi">Lundi</option>
                                <option value="Mardi">Mardi</option>
                                <option value="Mercredi">Mercredi</option>
                                <option value="Jeudi">Jeudi</option>
                                <option value="Vendredi">Vendredi</option>
                                <option value="Samedi">Samedi</option>
                                <option value="Dimanche">Dimanche</option>
                            </select>
                        </div>
                        <div class="col-xxl-2 col-sm-4">
                            <label class="form-label" for="start_date">DU</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-xxl-2 col-sm-4">
                            <label class="form-label" for="end_date">Au</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="">
                        </div>

                    </div>
                    <!--end row-->
                </div>

                <div class="card-body pt-0 mt-3">
                    <div class="row">
                        <div class="col-12">
                            @php
                                $thead = ['date vol', 'numero','Type APP','Capacite', 'Assist','arrive', 'heure arrive', 'depart', 'heure depart','min_date','max_date'];
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
        document.addEventListener('DOMContentLoaded', function () {
            let Table = $('#vols').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('vol.data_saisonnier') }}",
                    data: function (d) {
                        // Add day_of_week filter to the request
                        d.day_of_week = $('#day_of_week').val();
                        d.mouvement = $('#mouvement').val();
                        d.month = $('#month').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.seasonId = $('#seasonId').val();
                    }
                },
                columns: [
                    { data: 'jour_semaine', name: 'jour_semaine' },
                    { data: 'numero', name: 'numero' },
                    { data: 'equipement', name: 'equipement' },
                    { data: 'capacite', name: 'capacite' },
                    { data: 'assist', name: 'assist' },
                    { data: 'arrivee', name: 'arrivee' },
                    { data: 'heure_arrive', name: 'heure_arrive' },
                    { data: 'depart', name: 'depart' },
                    { data: 'heure_depart', name: 'heure_depart' },
                    { data: 'date_vol_min', name: 'date_vol_min' },
                    { data: 'date_vol_max', name: 'date_vol_max' }
                ]
            });

            // Event listener for the day filter
            $('#day_of_week, #mouvement,#month,#start_date, #end_date,#seasonId').on('change', function () {
                Table.draw();
            });
        });
    </script>

@endsection

