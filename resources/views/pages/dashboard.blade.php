@extends('layouts.master')
@section('title')
    @lang('Gestion des vols')
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/jsvectormap/css/jsvectormap.min.css') }}" rel="stylesheet">
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Dashboards
        @endslot
        @slot('title')
            Analytics
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-xxl-12">
            <div class="col-xxl-2 col-sm-3 text-center">

                <label class="form-label" for="">Saison</label>

                <select class="form-control" name="saison" id="saison">

                    <option value="">Select Season</option>

                    @foreach($saisons as $saison)

                        <option value="{{ $saison->id }}">{{ $saison->annee }}</option>

                    @endforeach

                </select>

            </div>
            <div class="d-flex flex-column h-100">

                <div class="row">

                    <div class="col-md-5">

                        <div class="card card-animate">
                            <div class="card-header bg-primary text-white h5">
                                Statistiques de vol</div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between">

                                    <div class="col-4">
                                        <div class="text-center">
                                            <p class="fw-medium text-muted mb-0">Arrivals</p>
                                            <h5 class="mt-4 ff-secondary fw-semibold" data-type="count_arrivees">0</h5>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-center">
                                            <p class="fw-medium text-muted mb-0">Departures</p>
                                            <h5 class="mt-4 ff-secondary fw-semibold" data-type="count_departs">0</h5>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-center">
                                            <p class="fw-medium text-muted mb-0">Total Flights</p>
                                            <h5 class="mt-4 ff-secondary fw-semibold" data-type="count_all">0</h5>
                                        </div>
                                    </div>

                                </div>
                            </div><!-- end card body -->
                        </div> <!-- end card-->
                    </div> <!-- end col-->

                    <div class="col-md-7">
                        <div class="card card-animate">
                            <div class="card-header bg-primary text-white h5">Statistiques de vol semaine plus chargé</div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    

                                        <!-- Semaine Section -->
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <p class="fw-medium text-muted mb-0">Semaine</p>
                                                <h5 class="mt-4 ff-secondary fw-semibold" data-type="semaine">0</h5>
                                            </div>
                                        </div>

                                        <!-- Périod Section -->
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <p class="fw-medium text-muted mb-0">Périod</p>
                                                <h5 class="mt-4 ff-secondary fw-semibold" data-type="period_semaine">0</h5>
                                            </div>
                                        </div>

                                        <!-- Arrivals for Semaine -->
                                        <div class="col-md-2">
                                            <div class="text-center">
                                                <p class="fw-medium text-muted mb-0">Arrivals</p>
                                                <h5 class="mt-4 ff-secondary fw-semibold" data-type="count_arrivees_semaine">0</h5>
                                            </div>
                                        </div>

                                        <!-- Departures for Semaine -->
                                        <div class="col-md-2">
                                            <div class="text-center">
                                                <p class="fw-medium text-muted mb-0">Departures</p>
                                                <h5 class="mt-4 ff-secondary fw-semibold" data-type="count_departs_semaine">0</h5>
                                            </div>
                                        </div>

                                        <!-- Total Vols for Semaine -->
                                        <div class="col-md-2">
                                            <div class="text-center">
                                                <p class="fw-medium text-muted mb-0">Total Vols</p>
                                                <h5 class="mt-4 ff-secondary fw-semibold" data-type="count_all_semaine">0</h5>
                                            </div>
                                        </div>
                                        <hr>



                                </div>
                            </div><!-- end card body -->
                        </div> <!-- end card-->
                    </div> <!-- end col-->
                </div> <!-- end row-->

                <div class="row">
                    <div class="col-md-5">
                        <div class="card card-animate">
                            <div class="card-header bg-primary text-white h5">Statistiques de vol mensuelles plus chargé</div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div class="col-3">
                                        <div class="text-center">
                                            <p class="fw-medium text-muted mb-0">Mois</p>
                                            <h5 class="mt-4 ff-secondary fw-semibold" data-type="mois">0</h5>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="text-center">
                                            <p class="fw-medium text-muted mb-0">Arrivals</p>
                                            <h5 class="mt-4 ff-secondary fw-semibold" data-type="count_arrivees_mois">0</h5>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="text-center">
                                            <p class="fw-medium text-muted mb-0">Departures</p>
                                            <h5 class="mt-4 ff-secondary fw-semibold" data-type="count_departs_mois">0</h5>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="text-center">
                                            <p class="fw-medium text-muted mb-0">Total Flights</p>
                                            <h5 class="mt-4 ff-secondary fw-semibold" data-type="count_all_mois">0</h5>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div> <!-- end card-->
                    </div> <!-- end col-->

                    <div class="col-md-7">
                        <div class="card card-animate">
                            <div class="card-header bg-primary text-white h5">Statistiques de vol jours plus chargé</div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between">



                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <p class="fw-medium text-muted mb-0">Jours</p>
                                                <h5 class="mt-4 ff-secondary fw-semibold" data-type="jours">0</h5>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="text-center">
                                                <p class="fw-medium text-muted mb-0">Arrivals</p>
                                                <h5 class="mt-4 ff-secondary fw-semibold" data-type="count_arrivees_jours">0</h5>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <p class="fw-medium text-muted mb-0">Departures</p>
                                                <h5 class="mt-4 ff-secondary fw-semibold" data-type="count_departs_jours">0</h5>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <p class="fw-medium text-muted mb-0">Total Flights</p>
                                                <h5 class="mt-4 ff-secondary fw-semibold" data-type="count_all_jours">0</h5>
                                            </div>
                                        </div>
                                        <hr>


                                </div>
                            </div><!-- end card body -->
                        </div> <!-- end card-->
                    </div> <!-- end col-->
                </div> <!-- end row-->
            </div>
        </div> <!-- end col-->
    </div> <!-- end row-->
    <div class="row  justify-content-center">
        <h1 class="mb-4 text-center">Statistiques de vols hebdomadaires</h1>
        <canvas id="myChart" class="h-75 w-75 "></canvas>
    </div>
@endsection
@section('script')
    {{-- <script>
        document.addEventListener('DOMContentLoaded', function () {
            const saisonSelect = document.getElementById('saison');

            saisonSelect.addEventListener('change', function() {
                const selectedSaison = this.value;

                if (selectedSaison) {
                    fetch(`/Count/${selectedSaison}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Received data:', data);

                            // Count all (assuming this is a single value, not an array)
                            document.querySelector("h5[data-type='count_arrivees']").textContent = data.Count_all.count_arrivees || 0;
                            document.querySelector("h5[data-type='count_departs']").textContent = data.Count_all.count_departs || 0;
                            document.querySelector("h5[data-type='count_all']").textContent = data.Count_all.count_all || 0;

                            // semaine chargee
                            if (data.Count_semaine_charge.length > 0) {
                                const currentWeek = data.Count_semaine_charge[0]; // Get the most recent week data
                                document.querySelector("h5[data-type='semaine']").textContent = currentWeek.week_year || 0;
                                document.querySelector("h5[data-type='period_semaine']").textContent = currentWeek.week_period || 0;
                                document.querySelector("h5[data-type='count_arrivees_semaine']").textContent = currentWeek.count_arrivee || 0;
                                document.querySelector("h5[data-type='count_departs_semaine']").textContent = currentWeek.count_depart || 0;
                                document.querySelector("h5[data-type='count_all_semaine']").textContent = currentWeek.count_all || 0;
                            }

                            // mois chargee
                            if (data.Count_mois_charge.length > 0) {
                                const currentMonth = data.Count_mois_charge[0]; // Get the most recent month data
                                document.querySelector("h5[data-type='mois']").textContent = currentMonth.month || 0;
                                document.querySelector("h5[data-type='count_arrivees_mois']").textContent = currentMonth.count_arrives || 0;
                                document.querySelector("h5[data-type='count_departs_mois']").textContent = currentMonth.count_departs || 0;
                                document.querySelector("h5[data-type='count_all_mois']").textContent = currentMonth.count_vols || 0;
                            }

                            // jour chargee
                            if (data.count_jours_charge.length > 0) {
                                const today = data.count_jours_charge[0]; // Get the most recent day data
                                document.querySelector("h5[data-type='jours']").textContent = today.date_vol || 0;
                                document.querySelector("h5[data-type='count_arrivees_jours']").textContent = today.count_arrivee || 0;
                                document.querySelector("h5[data-type='count_departs_jours']").textContent = today.count_depart || 0;
                                document.querySelector("h5[data-type='count_all_jours']").textContent = today.count_all || 0;
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching stats:', error);
                        });
                }
            });
        });
    </script> --}}
    <script>
        let array_count = @json($array_count_all);
        let array_count_mois = @json($array_count_mois);
        let array_count_semaine = @json($array_count_semaine);
        let array_count_jeurs = @json($array_count_jeurs);

        $("#saison").on('change', function () {
            let id_saison = $(this).val(); // Get the selected season ID

            console.log('Selected Season ID:', id_saison);

            // count all
            if (array_count && array_count[id_saison]) {
                let count_arrivees = array_count[id_saison]['count_arrivee'] || 0;
                let count_departs = array_count[id_saison]['count_depart'] || 0;
                let count_all = array_count[id_saison]['count_all'] || 0;

                // Update the HTML with the values
                $('[data-type="count_arrivees"]').text(count_arrivees);
                $('[data-type="count_departs"]').text(count_departs);
                $('[data-type="count_all"]').text(count_all);
            } else {
                // If no data exists, reset the counts to 0
                $('[data-type="count_arrivees"]').text(0);
                $('[data-type="count_departs"]').text(0);
                $('[data-type="count_all"]').text(0);
            }
            //mois chargee
            if (array_count_mois && array_count_mois[id_saison]) {
                let month = array_count_mois[id_saison]['month'] || 0;
                let count_arrivee = array_count_mois[id_saison]['count_arrivee'] || 0;
                let count_depart = array_count_mois[id_saison]['count_depart'] || 0;
                let count_all = array_count_mois[id_saison]['count_all'] || 0;

                $('[data-type="mois"]').text(month);
                $('[data-type="count_arrivees_mois"]').text(count_arrivee);
                $('[data-type="count_departs_mois"]').text(count_depart);
                $('[data-type="count_all_mois"]').text(count_all);
            } else {
                $('[data-type="mois"]').text(0);
                $('[data-type="count_arrivees_mois"]').text(0);
                $('[data-type="count_departs_mois"]').text(0);
                $('[data-type="count_all_mois"]').text(0);
            }
            // semaine charge
            if (array_count_semaine && array_count_semaine[id_saison]) {
                let week_year = array_count_semaine[id_saison]['week_year'] || 0;
                let periode = array_count_semaine[id_saison]['periode'] || 0;
                let arrivee_count = array_count_semaine[id_saison]['arrivee_count'] || 0;
                let depart_count = array_count_semaine[id_saison]['depart_count'] || 0;
                let total_flights = array_count_semaine[id_saison]['total_flights'] || 0;

                $('[data-type="semaine"]').text(week_year);
                $('[data-type="period_semaine"]').text(periode);
                $('[data-type="count_arrivees_semaine"]').text(arrivee_count);
                $('[data-type="count_departs_semaine"]').text(depart_count);
                $('[data-type="count_all_semaine"]').text(total_flights);
            } else {
                $('[data-type="semaine"]').text(0);
                $('[data-type="period_semaine"]').text(0);
                $('[data-type="count_arrivees_semaine"]').text(0);
                $('[data-type="count_departs_semaine"]').text(0);
                $('[data-type="count_all_semaine"]').text(0);
            }
            // jour charge
            if (array_count_jeurs && array_count_jeurs[id_saison]) {
            let date_vol = array_count_jeurs[id_saison]['date_vol'] || 0;
            let count_arrivee = array_count_jeurs[id_saison]['count_arrivee'] || 0;
            let count_depart = array_count_jeurs[id_saison]['count_depart'] || 0;
            let count_all = array_count_jeurs[id_saison]['count_all'] || 0;

            // Update the first set of data
            $('[data-type="jours"]').text(date_vol);
            $('[data-type="count_arrivees_jours"]').text(count_arrivee);
            $('[data-type="count_departs_jours"]').text(count_depart);
            $('[data-type="count_all_jours"]').text(count_all);
        } else {
            // If no data for id_saison, set all to 0
            $('[data-type="jours"]').text(0);
            $('[data-type="count_arrivees_jours"]').text(0);
            $('[data-type="count_departs_jours"]').text(0);
            $('[data-type="count_all_jours"]').text(0);
    }
        });
    </script>





    {{-- <script>
        document.addEventListener('DOMContentLoaded', function () {
            var ctx = document.getElementById('myChart').getContext('2d');
            var stats = @json($Count_somaine_charts ?? ['arrivees' => 0, 'departs' => 0, 'total' => 0]);

            var labels = stats.map(stat => stat.week_year);
            var flightCounts = stats.map(stat => stat.count_all);
            var departCounts = stats.map(stat => stat.count_depart);
            var arriveeCounts = stats.map(stat => stat.count_arrivee);

            var myChart = new Chart(ctx, {
                type: 'line', // You can change this to 'line', 'pie', etc.
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Total Flights',
                            data: flightCounts,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 2
                        },
                        {
                            label: 'Departures from AGA',
                            data: departCounts,
                            backgroundColor: 'rgba(255, 159, 64, 0.2)',
                            borderColor: 'rgba(255, 159, 64, 1)',
                            borderWidth: 2
                        },
                        {
                            label: 'Arrivals to AGA',
                            data: arriveeCounts,
                            backgroundColor: 'rgba(153, 102, 255, 0.2)',
                            borderColor: 'rgba(153, 102, 255, 1)',
                            borderWidth: 3
                        }
                    ]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script> --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var ctx = document.getElementById('myChart').getContext('2d');
            var myChart;

            // Function to create or update the chart
            function createOrUpdateChart(data) {
                var stats = data || []; // Default to empty array if no data
                var labels = stats.map(stat => stat.week_year);
                var arriveeCounts = stats.map(stat => stat.count_arrivee);
                var departCounts = stats.map(stat => stat.count_depart);
                var flightCounts = stats.map(stat => stat.count_all);

                // If the chart already exists, destroy it before creating a new one
                if (myChart) {
                    myChart.destroy();
                }

                // Create the chart
                myChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Total Flights',
                                data: flightCounts,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 2
                            },
                            {
                                label: 'Departures from AGA',
                                data: departCounts,
                                backgroundColor: 'rgba(255, 159, 64, 0.2)',
                                borderColor: 'rgba(255, 159, 64, 1)',
                                borderWidth: 2
                            },
                            {
                                label: 'Arrivals to AGA',
                                data: arriveeCounts,
                                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                                borderColor: 'rgba(153, 102, 255, 1)',
                                borderWidth: 3
                            }
                        ]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Fetch and update the chart with new data based on the selected season
            function fetchChartData(saisonId) {
                fetch(`/Count/${saisonId}`)
                    .then(response => response.json())
                    .then(data => {
                        // Update the chart with new data
                        createOrUpdateChart(data.Count_somaine_charts);
                    })
                    .catch(error => {
                        console.error('Error fetching season data:', error);
                    });
            }

            // Event listener for season dropdown change
            document.getElementById('saison').addEventListener('change', function (event) {
                var selectedSaisonId = event.target.value;

                // If a season is selected, fetch the new data
                if (selectedSaisonId) {
                    fetchChartData(selectedSaisonId);
                } else {
                    // Optionally reset the chart if no season is selected
                    if (myChart) {
                        myChart.destroy();
                    }
                }
            });

            // Optionally initialize chart with default data from the server
            var initialStats = @json($Count_somaine_charts ?? []);
            createOrUpdateChart(initialStats);
        });
    </script>



@endsection
@section('script')
    <!-- apexcharts -->
    <script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/jsvectormap/js/jsvectormap.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/jsvectormap/maps/world-merc.js') }}"></script>

    <!-- dashboard init -->
    <script src="{{ URL::asset('build/js/pages/dashboard-analytics.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    {{-- chjatrs --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection
