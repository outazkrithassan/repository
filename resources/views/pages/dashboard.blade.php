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
                                    <div class="row mb-3">

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
    {{-- <script>
        document.addEventListener('DOMContentLoaded', function () {
            const saisonSelect = document.getElementById('saison');

            saisonSelect.addEventListener('change', function() {
                const selectedSaison = this.value;

                if (selectedSaison) {
                    fetch(`/Count/${selectedSaison}`)
                        .then(response => response.json())
                        .then(data => {
                            // Update statistics for Arrivals, Departures, and Total Flights
                            document.querySelector("h5[data-type='count_arrivees']").textContent = data.Count_all.count_arrivees;
                            document.querySelector("h5[data-type='count_departs']").textContent = data.Count_all.count_departs;
                            document.querySelector("h5[data-type='count_all']").textContent = data.Count_all.count_all;

                            // Update weekly stats
                            let weekHtml = '';
                            data.Count_all.forEach(stat => {
                                weekHtml += `
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <p class="fw-medium text-muted mb-0">Jours</p>
                                            <h5 class="mt-4 ff-secondary fw-semibold">${stat.count_arrivees}</h5>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <p class="fw-medium text-muted mb-0">Arrivals</p>
                                            <h5 class="mt-4 ff-secondary fw-semibold">${stat.count_departs}</h5>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <p class="fw-medium text-muted mb-0">Departures</p>
                                            <h5 class="mt-4 ff-secondary fw-semibold">${stat.count_all}</h5>
                                        </div>
                                    </div>

                                    <hr>
                                `;
                            });
                            document.querySelector('.row.mb-3').innerHTML = weekHtml;

                            // Update monthly and daily stats similarly...
                        })
                        .catch(error => console.error('Error fetching stats:', error));
                }
            });
        });
    </script> --}}
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

                            // Count all
                            document.querySelector("h5[data-type='count_arrivees']").textContent = data.Count_all.count_arrivees || 0;
                            document.querySelector("h5[data-type='count_departs']").textContent = data.Count_all.count_departs || 0;
                            document.querySelector("h5[data-type='count_all']").textContent = data.Count_all.count_all || 0;

                            // semaine chargee
                            document.querySelector("h5[data-type='semaine']").textContent = data.Count_semaine_charge.week_year || 0;
                            document.querySelector("h5[data-type='period_semaine']").textContent = data.Count_semaine_charge.week_period || 0;
                            document.querySelector("h5[data-type='count_arrivees_semaine']").textContent = data.Count_semaine_charge.count_arrivee || 0;
                            document.querySelector("h5[data-type='count_departs_semaine']").textContent = data.Count_semaine_charge.count_depart || 0;
                            document.querySelector("h5[data-type='count_all_semaine']").textContent = data.Count_semaine_charge.count_all || 0;

                            // mois chargee
                            document.querySelector("h5[data-type='mois']").textContent = data.Count_mois_charge.month || 0;
                            document.querySelector("h5[data-type='count_arrivees_mois']").textContent = data.Count_mois_charge.count_arrives || 0;
                            document.querySelector("h5[data-type='count_departs_mois']").textContent = data.Count_mois_charge.count_departs || 0;
                            document.querySelector("h5[data-type='count_all_mois']").textContent = data.Count_mois_charge.count_vols || 0;

                            // jour chargee
                            document.querySelector("h5[data-type='jours']").textContent = data.count_jours_charge.date_vol || 0;
                            document.querySelector("h5[data-type='count_arrivees_jours']").textContent = data.count_jours_charge.count_arrivee || 0;
                            document.querySelector("h5[data-type='count_departs_jours']").textContent = data.count_jours_charge.count_depart || 0;
                            document.querySelector("h5[data-type='count_all_jours']").textContent = data.count_jours_charge.count_all || 0;
                        })
                        .catch(error => {
                            console.error('Error fetching stats:', error);
                        });
                }
            });
        });
    </script> --}}
    <script>
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
                // Use the passed 'data' argument for dynamic updates
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


    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header border-0 align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Audiences Metrics</h4>
                    <div>
                        <button type="button" class="btn btn-soft-secondary btn-sm">
                            ALL
                        </button>
                        <button type="button" class="btn btn-soft-secondary btn-sm">
                            1M
                        </button>
                        <button type="button" class="btn btn-soft-secondary btn-sm">
                            6M
                        </button>
                        <button type="button" class="btn btn-soft-primary btn-sm">
                            1Y
                        </button>
                    </div>
                </div><!-- end card header -->
                <div class="card-header p-0 border-0 bg-light-subtle">
                    <div class="row g-0 text-center">
                        <div class="col-6 col-sm-4">
                            <div class="p-3 border border-dashed border-start-0">
                                <h5 class="mb-1"><span class="counter-value" data-target="854">0</span>
                                    <span class="text-success ms-1 fs-13">49%<i
                                            class="ri-arrow-right-up-line ms-1 align-middle"></i></span>
                                </h5>
                                <p class="text-muted mb-0">Avg. Session</p>
                            </div>
                        </div>
                        <!--end col-->
                        <div class="col-6 col-sm-4">
                            <div class="p-3 border border-dashed border-start-0">
                                <h5 class="mb-1"><span class="counter-value" data-target="1278">0</span>
                                    <span class="text-success ms-1 fs-13">60%<i
                                            class="ri-arrow-right-up-line ms-1 align-middle"></i></span>
                                </h5>
                                <p class="text-muted mb-0">Conversion Rate</p>
                            </div>
                        </div>
                        <!--end col-->
                        <div class="col-6 col-sm-4">
                            <div class="p-3 border border-dashed border-start-0 border-end-0">
                                <h5 class="mb-1"><span class="counter-value" data-target="3">0</span>m
                                    <span class="counter-value" data-target="40">0</span>sec
                                    <span class="text-success ms-1 fs-13">37%<i
                                            class="ri-arrow-right-up-line ms-1 align-middle"></i></span>
                                </h5>
                                <p class="text-muted mb-0">Avg. Session Duration</p>
                            </div>
                        </div>
                        <!--end col-->
                    </div>
                </div><!-- end card header -->
                <div class="card-body p-0 pb-2">
                    <div>
                        <div id="audiences_metrics_charts" data-colors='["--vz-primary", "--vz-light"]'
                            class="apex-charts" dir="ltr"></div>
                    </div>
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->

        <div class="col-xl-6">
            <div class="card card-height-100">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Audiences Sessions by Country</h4>
                    <div class="flex-shrink-0">
                        <div class="dropdown card-header-dropdown">
                            <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <span class="fw-semibold text-uppercase fs-13">Sort by: </span><span
                                    class="text-muted">Current Week<i class="mdi mdi-chevron-down ms-1"></i></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#">Today</a>
                                <a class="dropdown-item" href="#">Last Week</a>
                                <a class="dropdown-item" href="#">Last Month</a>
                                <a class="dropdown-item" href="#">Current Year</a>
                            </div>
                        </div>
                    </div>
                </div><!-- end card header -->
                <div class="card-body p-0">
                    <div>
                        <div id="audiences-sessions-country-charts" data-colors='["--vz-success", "--vz-secondary"]'
                            class="apex-charts" dir="ltr"></div>
                    </div>
                </div><!-- end cardbody -->
            </div><!-- end card -->
        </div><!-- end col -->
    </div><!-- end row -->

    <div class="row">
        <div class="col-xl-4">
            <div class="card card-height-100">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Users by Device</h4>
                    <div class="flex-shrink-0">
                        <div class="dropdown card-header-dropdown">
                            <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <span class="text-muted fs-16"><i class="mdi mdi-dots-vertical align-middle"></i></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#">Today</a>
                                <a class="dropdown-item" href="#">Last Week</a>
                                <a class="dropdown-item" href="#">Last Month</a>
                                <a class="dropdown-item" href="#">Current Year</a>
                            </div>
                        </div>
                    </div>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="user_device_pie_charts" data-colors='["--vz-primary", "--vz-warning", "--vz-info"]'
                        class="apex-charts" dir="ltr"></div>

                    <div class="table-responsive mt-3">
                        <table class="table table-borderless table-sm table-centered align-middle table-nowrap mb-0">
                            <tbody class="border-0">
                                <tr>
                                    <td>
                                        <h4 class="text-truncate fs-14 fs-medium mb-0"><i
                                                class="ri-stop-fill align-middle fs-18 text-primary me-2"></i>Desktop
                                            Users</h4>
                                    </td>
                                    <td>
                                        <p class="text-muted mb-0"><i data-feather="users"
                                                class="me-2 icon-sm"></i>78.56k</p>
                                    </td>
                                    <td class="text-end">
                                        <p class="text-success fw-medium fs-13 mb-0"><i
                                                class="ri-arrow-up-s-fill fs-5 align-middle"></i>2.08%
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h4 class="text-truncate fs-14 fs-medium mb-0"><i
                                                class="ri-stop-fill align-middle fs-18 text-warning me-2"></i>Mobile
                                            Users</h4>
                                    </td>
                                    <td>
                                        <p class="text-muted mb-0"><i data-feather="users"
                                                class="me-2 icon-sm"></i>105.02k</p>
                                    </td>
                                    <td class="text-end">
                                        <p class="text-danger fw-medium fs-13 mb-0"><i
                                                class="ri-arrow-down-s-fill fs-5 align-middle"></i>10.52%
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h4 class="text-truncate fs-14 fs-medium mb-0"><i
                                                class="ri-stop-fill align-middle fs-18 text-info me-2"></i>Tablet
                                            Users</h4>
                                    </td>
                                    <td>
                                        <p class="text-muted mb-0"><i data-feather="users"
                                                class="me-2 icon-sm"></i>42.89k</p>
                                    </td>
                                    <td class="text-end">
                                        <p class="text-danger fw-medium fs-13 mb-0"><i
                                                class="ri-arrow-down-s-fill fs-5 align-middle"></i>7.36%
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->

        <div class="col-xl-4 col-md-6">
            <div class="card card-height-100">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Top Referrals Pages</h4>
                    <div class="flex-shrink-0">
                        <button type="button" class="btn btn-soft-primary btn-sm">
                            Export Report
                        </button>
                    </div>
                </div>

                <div class="card-body">

                    <div class="row align-items-center">
                        <div class="col-6">
                            <h6 class="text-muted text-uppercase fw-semibold text-truncate fs-13 mb-3">
                                Total Referrals Page</h6>
                            <h4 class="mb-0">725,800</h4>
                            <p class="mb-0 mt-2 text-muted"><span class="badge bg-success-subtle text-success mb-0">
                                    <i class="ri-arrow-up-line align-middle"></i> 15.72 %
                                </span> vs. previous month</p>
                        </div><!-- end col -->
                        <div class="col-6">
                            <div class="text-center">
                                <img src="{{ URL::asset('build/images/illustrator-1.png') }}" class="img-fluid"
                                    alt="">
                            </div>
                        </div><!-- end col -->
                    </div><!-- end row -->
                    <div class="mt-3 pt-2">
                        <div class="progress progress-lg rounded-pill">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 25%"
                                aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                            <div class="progress-bar bg-info" role="progressbar" style="width: 18%" aria-valuenow="18"
                                aria-valuemin="0" aria-valuemax="100"></div>
                            <div class="progress-bar bg-success" role="progressbar" style="width: 22%"
                                aria-valuenow="22" aria-valuemin="0" aria-valuemax="100"></div>
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 16%"
                                aria-valuenow="16" aria-valuemin="0" aria-valuemax="100"></div>
                            <div class="progress-bar bg-danger" role="progressbar" style="width: 19%" aria-valuenow="19"
                                aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div><!-- end -->

                    <div class="mt-3 pt-2">
                        <div class="d-flex mb-2">
                            <div class="flex-grow-1">
                                <p class="text-truncate text-muted fs-15 mb-0"><i
                                        class="mdi mdi-circle align-middle text-primary me-2"></i>www.google.com
                                </p>
                            </div>
                            <div class="flex-shrink-0">
                                <p class="mb-0">24.58%</p>
                            </div>
                        </div><!-- end -->
                        <div class="d-flex mb-2">
                            <div class="flex-grow-1">
                                <p class="text-truncate text-muted fs-15 mb-0"><i
                                        class="mdi mdi-circle align-middle text-info me-2"></i>www.youtube.com
                                </p>
                            </div>
                            <div class="flex-shrink-0">
                                <p class="mb-0">17.51%</p>
                            </div>
                        </div><!-- end -->
                        <div class="d-flex mb-2">
                            <div class="flex-grow-1">
                                <p class="text-truncate text-muted fs-15 mb-0"><i
                                        class="mdi mdi-circle align-middle text-success me-2"></i>www.meta.com
                                </p>
                            </div>
                            <div class="flex-shrink-0">
                                <p class="mb-0">23.05%</p>
                            </div>
                        </div><!-- end -->
                        <div class="d-flex mb-2">
                            <div class="flex-grow-1">
                                <p class="text-truncate text-muted fs-15 mb-0"><i
                                        class="mdi mdi-circle align-middle text-warning me-2"></i>www.medium.com
                                </p>
                            </div>
                            <div class="flex-shrink-0">
                                <p class="mb-0">12.22%</p>
                            </div>
                        </div><!-- end -->
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-truncate text-muted fs-15 mb-0"><i
                                        class="mdi mdi-circle align-middle text-danger me-2"></i>Other
                                </p>
                            </div>
                            <div class="flex-shrink-0">
                                <p class="mb-0">17.58%</p>
                            </div>
                        </div><!-- end -->
                    </div><!-- end -->

                    <div class="mt-2 text-center">
                        <a href="javascript:void(0);" class="text-muted text-decoration-underline">Show
                            All</a>
                    </div>

                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->

        <div class="col-xl-4 col-md-6">
            <div class="card card-height-100">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Top Pages</h4>
                    <div class="flex-shrink-0">
                        <div class="dropdown card-header-dropdown">
                            <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <span class="text-muted fs-16"><i class="mdi mdi-dots-vertical align-middle"></i></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#">Today</a>
                                <a class="dropdown-item" href="#">Last Week</a>
                                <a class="dropdown-item" href="#">Last Month</a>
                                <a class="dropdown-item" href="#">Current Year</a>
                            </div>
                        </div>
                    </div>
                </div><!-- end card header -->
                <div class="card-body">
                    <div class="table-responsive table-card">
                        <table class="table align-middle table-borderless table-centered table-nowrap mb-0">
                            <thead class="text-muted table-light">
                                <tr>
                                    <th scope="col" style="width: 62;">Active Page</th>
                                    <th scope="col">Active</th>
                                    <th scope="col">Users</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <a href="javascript:void(0);">/themesbrand/skote-25867</a>
                                    </td>
                                    <td>99</td>
                                    <td>25.3%</td>
                                </tr><!-- end -->
                                <tr>
                                    <td>
                                        <a href="javascript:void(0);">/dashonic/chat-24518</a>
                                    </td>
                                    <td>86</td>
                                    <td>22.7%</td>
                                </tr><!-- end -->
                                <tr>
                                    <td>
                                        <a href="javascript:void(0);">/skote/timeline-27391</a>
                                    </td>
                                    <td>64</td>
                                    <td>18.7%</td>
                                </tr><!-- end -->
                                <tr>
                                    <td>
                                        <a href="javascript:void(0);">/themesbrand/minia-26441</a>
                                    </td>
                                    <td>53</td>
                                    <td>14.2%</td>
                                </tr><!-- end -->
                                <tr>
                                    <td>
                                        <a href="javascript:void(0);">/dashon/dashboard-29873</a>
                                    </td>
                                    <td>33</td>
                                    <td>12.6%</td>
                                </tr><!-- end -->
                                <tr>
                                    <td>
                                        <a href="javascript:void(0);">/doot/chats-29964</a>
                                    </td>
                                    <td>20</td>
                                    <td>10.9%</td>
                                </tr><!-- end -->
                                <tr>
                                    <td>
                                        <a href="javascript:void(0);">/minton/pages-29739</a>
                                    </td>
                                    <td>10</td>
                                    <td>07.3%</td>
                                </tr><!-- end -->
                            </tbody><!-- end tbody -->
                        </table><!-- end table -->
                    </div><!-- end -->
                </div><!-- end cardbody -->
            </div><!-- end card -->
        </div><!-- end col -->
    </div><!-- end row -->
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
