@extends('layouts.master')
@section('title')
    Semaine plus que charger
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
            semaine
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card" id="orderList">
                <div class="card-header border-0">
                    <div class="row align-items-center gy-3">
                        <div class="col-sm">
                            <h5 class="card-title mb-0">Pregramme somaine plus que charge</h5>
                        </div>
                    </div>
                </div>

                <div class="card-body border border-dashed border-end-0 border-start-0">
                    <div class="row g-3">
                        <div class="col-xxl-4 col-sm-4">
                            <label class="form-label" for="">Saison</label>
                            <select class="form-control" name="selectedSeason" id="selectedSeason">
                                <option value="">Select Season</option>
                                @foreach($saisons as $saison)
                                    <option value="{{ $saison->id }}">{{ $saison->annee }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xxl-4 col-sm-4">
                            <label class="form-label" for="">Mouvement</label>
                            <select class="form-control" name="movment" id="movment">
                                <option value="">Arrivée/Depart</option>
                                <option value="-1">Arrivée</option>
                                <option value="1">Depart</option>
                            </select>
                        </div>
                        <div class="col-xxl-4 col-sm-4">
                            <label class="form-label" for="">Jour</label>
                            <select class="form-control" name="selectedDate" id="selectedDate">

                                <!-- Days will be populated here -->
                            </select>
                        </div>

                    </div>
                    <!--end row-->
                </div>

                <div class="card-body pt-0 mt-3">
                    <div class="row">
                        <div class="col-12">
                            @php
                                $thead = ['date vol', 'numero','Type APP','Capacite','Assist', 'arrive', 'heure arrive', 'depart', 'heure depart'];
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
                    url: "{{ route('vol.data_somaine') }}",
                    data: function (d) {
                        // Add day_of_week filter to the request
                        d.selectedDate = $('#selectedDate').val();
                        d.movment = $('#movment').val();
                        d.selectedSeason = $('#selectedSeason').val();

                    }
                },
                columns: [
                    { data: 'flight_date', name: 'flight_date' },
                    { data: 'numero', name: 'numero' },
                    { data: 'equipement', name: 'equipement' },
                    { data: 'capacite', name: 'capacite' },
                    { data: 'assist', name: 'assist' },
                    { data: 'arrivee', name: 'arrivee' },
                    { data: 'heure_arrive', name: 'heure_arrive' },
                    { data: 'depart', name: 'depart' },
                    { data: 'heure_depart', name: 'heure_depart' }

                ]
            });

            // Event listener for the day filter
            $('#selectedDate,#movment,#selectedSeason').on('change', function () {
                Table.draw();
            });
        });
    </script>
    <script>
        let array_saisons = @json($array); // Convert PHP array to JavaScript

        $("#selectedSeason").on('change', function () {
            let id_saison = $(this).val(); // Get the selected season ID

            console.log('Selected Season ID:', id_saison);
            // Clear the previous options in the date select element
            $('#selectedDate').empty();

            let newArray = array_saisons.filter(ele => ele.id_saison == id_saison) ;
            // Check if there are any days for the selected season

                newArray.days.split(',').forEach(ele => {
                    // Append new options to the select element
                    $('#selectedDate').append(`<option value="${ele}">${ele}, ${ele.formattedDate}</option>`);
                });

        });
    </script>

@endsection
