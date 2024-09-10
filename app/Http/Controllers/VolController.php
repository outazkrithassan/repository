<?php

namespace App\Http\Controllers;

use App\Imports\Vols;
use App\Models\Vol;
use App\Models\VolDepart;
use App\Models\VolArrive;
use Illuminate\Http\Request;
use App\Exports\VolsExport;
use App\Exports\Vols_Heurs_Export;
use App\Exports\Vols_somaine_export;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class VolController extends Controller
{

    public function saisonnier()
    {
        $saisons = DB::table('saisons')->get();

        return view('pages.programme.saisonnier', compact('saisons'));
    }

    public function somaine(Request $request)
    {

        // Fetch all saisons
        $saisons = DB::table('saisons')->get();

        // Get the busiest week for each saison_id
        $results = DB::select("
            WITH RankedFlights AS (
                SELECT
                    DATE_FORMAT(date_vol, '%Y-%u') AS week_year,
                    saison_id,
                    COUNT(*) AS total_flights,
                    GROUP_CONCAT(DISTINCT DATE_FORMAT(date_vol, '%Y-%m-%d') ORDER BY date_vol ASC SEPARATOR ', ') AS days,
                    ROW_NUMBER() OVER (PARTITION BY saison_id ORDER BY COUNT(*) DESC, week_year DESC) AS row_num
                FROM (
                    SELECT date_vol, saison_id FROM vol_arrives
                    UNION ALL
                    SELECT date_vol, saison_id FROM vol_departs
                ) AS combined_flights
                GROUP BY week_year, saison_id
            )
            SELECT week_year, saison_id, total_flights, days
            FROM RankedFlights
            WHERE row_num = 1  -- One result for each saison_id
            ORDER BY saison_id;
        ");

        // Initialize the results array
        $array = [];

        // Iterate through the results
        foreach ($results as $row) {
            // Extract the days from the 'days' column
            $saison_id = $row->saison_id;  // Correctly assign saison_id
            $days = explode(',', $row->days);
            $formattedDays = [];

            foreach ($days as $day) {
                // Configure the locale for French days
                setlocale(LC_TIME, 'fr_FR.UTF-8');

                // Convert the date to the day name (in French)
                $dayName = Carbon::parse($day)->locale('fr')->translatedFormat('l');
                $formattedDate = date('d-m-Y', strtotime($day)); // Format the date
                $formattedDays[] = [
                    'dayName' => $dayName,
                    'formattedDate' => $formattedDate,
                    'date' => $day,
                ]; // Create an array of objects for each day
            }

            // Ensure unique formatted days for each saison
            if (!isset($array[$saison_id])) {
                $array[$saison_id] = []; // Initialize if not set
            }

            // Merge formatted days while ensuring uniqueness based on the date
            foreach ($formattedDays as $formattedDay) {
                // Check for existing dates to avoid duplicates
                if (!in_array($formattedDay['date'], array_column($array[$saison_id], 'date'))) {
                    $array[$saison_id][] = $formattedDay;
                }
            }
        }

        // Return the view with the saisons and array data
        return view('pages.programme.somaine', compact('saisons', 'array'));
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }




    /**
     * Display the specified resource.
     */
    public function show(Vol $vol)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vol $vol)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vol $vol)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vol $vol)
    {
        //
    }

    public function datatable_saisonnier1(Request $request)
    {
        $dayFilter = $request->input('day_of_week');
        $mouvementFilter = $request->input('mouvement');

        // SQL Queries
        $sql_arrive = "
                SELECT
                va.numero AS numero,
                va.depart AS depart,
                va.heure_arrive AS heure_arrive,
                va.date_vol AS date_vol,
                va.companie_id AS companie_id,
                a.equipement AS equipement,
                a.capacite AS capacite,
                c.nom AS company_name_arrive,
                ass.name AS assist_info
            FROM vol_arrives va
            JOIN avions a ON va.avion_id = a.id
            JOIN companies c ON va.companie_id = c.id
            LEFT JOIN name_assist ass ON c.nom= ass.code
            ORDER BY va.date_vol
        ";

        $sql_depart = "
            SELECT
                vd.numero AS numero,
                vd.destination AS destination,
                vd.heure_depart AS heure_depart,
                vd.date_vol AS date_vol,
                vd.companie_id AS companie_id,
                a.equipement AS equipement,
                a.capacite AS capacite,
                c.nom AS company_name_depart,
                ass.name AS assist_info
            FROM vol_departs vd
            JOIN avions a ON vd.avion_id = a.id
            JOIN companies c ON vd.companie_id = c.id
            LEFT JOIN name_assist ass ON c.nom= ass.code
            ORDER BY vd.date_vol
        ";

        // Fetch flight data
        $arrives = DB::select($sql_arrive);
        $departs = DB::select($sql_depart);

        // Fetch city codes
        $cityCodes = DB::table('city_codes')->get()->keyBy('code');

        $resultat = [];

        // Process arrivals
        foreach ($arrives as $arrive) {
            $freres = DB::table('vol_freres')
            ->where('numero_arrivee', $arrive->numero)
            ->pluck('numero_depart')
            ->toArray();
            $ccArrivee = $cityCodes->get($arrive->depart);
            $adjusted_date_vol = $arrive->heure_arrive >= '2310' && $arrive->heure_arrive <= '2359'
                ? date('Y-m-d', strtotime($arrive->date_vol . ' +1 day'))
                : $arrive->date_vol;

            $dayOfWeek = Carbon::createFromFormat('Y-m-d', $adjusted_date_vol)->locale('fr_FR')->isoFormat('dddd');

            if (!isset($resultat[$dayOfWeek][$arrive->numero])) {
                $resultat[$dayOfWeek][$arrive->numero] = [
                    "num_arrive" => $arrive->numero,
                    "depart" => $ccArrivee ? $ccArrivee->name : 'Unknown',
                    "heure_arrive" => substr($arrive->heure_arrive, 0, 2) . 'h' . substr($arrive->heure_arrive, 2, 2),
                    "num_depart" => "--",
                    "destination" => "--",
                    "heure_depart" => "--",
                    "equipement" => $arrive->equipement,
                    "capacite" => $arrive->capacite,
                    "company_name_arrive" => $arrive->company_name_arrive,
                    "company_name_depart" => null,
                    "date_vols" => [],
                    "freres" => $freres,
                    "assist_info" => $arrive->assist_info,
                ];
            }

            $resultat[$dayOfWeek][$arrive->numero]['date_vols'][] = $adjusted_date_vol;
        }

        // Process departures
        foreach ($departs as $depart) {
            $found = false;
            $dayOfWeek = Carbon::createFromFormat('Y-m-d', $depart->date_vol)->locale('fr_FR')->isoFormat('dddd');
            $ccDepart = $cityCodes->get($depart->destination);

            if (isset($resultat[$dayOfWeek])) {
                foreach ($resultat[$dayOfWeek] as &$vol) {
                    if (in_array($depart->numero, $vol['freres'])) {
                        $vol['num_depart'] = $depart->numero;
                        $vol['destination'] = $ccDepart ? $ccDepart->name : 'Unknown';
                        $vol['heure_depart'] = substr($depart->heure_depart, 0, 2) . 'h' . substr($depart->heure_depart, 2, 2);
                        $vol['company_name_depart'] = $depart->company_name_depart;
                        $vol['date_vols'][] = $depart->date_vol;
                        $found = true;
                        break;
                    }
                }
            }

            if (!$found) {
                if (!isset($resultat[$dayOfWeek][$depart->numero])) {
                    $resultat[$dayOfWeek][$depart->numero] = [
                        'num_arrive' => "--",
                        'depart' => "--",
                        'heure_arrive' => "--",
                        'num_depart' => $depart->numero,
                        'destination' => $ccDepart ? $ccDepart->name : 'Unknown',
                        'heure_depart' => substr($depart->heure_depart, 0, 2) . 'h' . substr($depart->heure_depart, 2, 2),
                        'equipement' => $depart->equipement,
                        'capacite' => $depart->capacite,
                        'company_name_arrive' => null,
                        'company_name_depart' => $depart->company_name_depart,
                        'date_vols' => [],
                        'freres' => [],
                        "assist_info" => $depart->assist_info
                    ];
                }
                $resultat[$dayOfWeek][$depart->numero]['date_vols'][] = $depart->date_vol;
            }
        }

        $flattenedResult = [];
        foreach ($resultat as $dayOfWeek => $vols) {
            foreach ($vols as $vol) {
                // Calculate min and max dates
                $min_date_vol = min($vol['date_vols']);
                $max_date_vol = max($vol['date_vols']);

                // Construct companyInfo
                $vcArrive = $vol['company_name_arrive'];
                $vcDepart = $vol['company_name_depart'];
                $separator = $vol['num_depart'] !== "--" ? '/' : '';
                $companyInfo = "";

                if ($vcArrive && $vcDepart) {
                    $companyInfo = $vcArrive . " " . $vol['num_arrive'] . $separator . "" . $vol['num_depart'];
                } elseif ($vcArrive) {
                    $companyInfo = $vcArrive . " " . $vol['num_arrive'];
                } elseif ($vcDepart) {
                    $companyInfo = $vcDepart . " " . $vol['num_depart'];
                }

                $flattenedResult[] = array_merge(['day_of_week' => $dayOfWeek], $vol, [
                    'company_info' => $companyInfo,
                    'min_date' => $min_date_vol,
                    'max_date' => $max_date_vol,
                ]);
            }
        }

        if ($dayFilter) {
            $flattenedResult = array_filter($flattenedResult, function ($vol) use ($dayFilter) {
                return $vol['day_of_week'] === $dayFilter;
            });
        }
        if ($mouvementFilter) {
            $flattenedResult = array_filter($flattenedResult, function ($vol) use ($mouvementFilter) {
                if ($mouvementFilter == '-1') {
                    // Show only arrivals
                    return $vol['num_arrive'] !== '--' && $vol['num_depart'] === '--';
                } elseif ($mouvementFilter == '1') {
                    // Show only departures
                    return $vol['num_depart'] !== '--' && $vol['num_arrive'] === '--';
                }
                // Show both arrivals and departures
                return true;
            });
        }

        // Use DataTables to transform the result into the desired format
        return DataTables::of($flattenedResult)
            ->addColumn('date_vol', function ($vol) {
                return $vol['day_of_week'];
            })
            ->addColumn('numero', function ($vol) {
                return $vol['num_arrive'] !== '--' ? $vol['num_arrive'] : $vol['num_depart'];
            })
            ->addColumn('equipement', function ($vol) {
                return $vol['equipement'];
            })
            ->addColumn('capacite', function ($vol) {
                return $vol['capacite'];
            })
            ->addColumn('arrivee', function ($vol) {
                return $vol['depart']; // City name for departure
            })
            ->addColumn('heure_arrive', function ($vol) {
                return $vol['heure_arrive'];
            })
            ->addColumn('depart', function ($vol) {
                return $vol['destination']; // City name for destination
            })
            ->addColumn('heure_depart', function ($vol) {
                return $vol['heure_depart'];
            })
            ->addColumn('company_info', function ($vol) {
                return $vol['company_info'];
            })
            ->addColumn('min_date', function ($vol) {
                return $vol['min_date'];
            })
            ->addColumn('max_date', function ($vol) {
                return $vol['max_date'];
            })
            ->addColumn('assist_info', function ($vol) {
                return $vol['assist_info']; // Add assist info
            })
            ->rawColumns(['date_vol', 'numero', 'equipement', 'capacite', 'arrivee', 'heure_arrive', 'depart', 'heure_depart', 'company_info','assist_info'])
            ->toJson();
    }
    public function datatable_saisonnierzzz(Request $request)
    {
        DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY',''))");

        $dayOfWeek = $request->input('day_of_week');
        $mouvement = $request->input('mouvement');
        $monthFilter = $request->input('month');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $seasonId = $request->input('seasonId');
        $whereClause = [];
        $bindings = [];

        // Filtering by day of the week
        if (!empty($dayOfWeek)) {
            $whereClause[] = "jour_semaine = :dayOfWeek";
            $bindings['dayOfWeek'] = strtolower($dayOfWeek); // Convert to lowercase to match the SQL CASE
        }

        // Filtering by "Arrivée/Depart"
        if (!empty($mouvement)) {
            if ($mouvement == '-1') { // Arrivée
                $whereClause[] = "numero_arrivee != '--' AND numero_depart = '--'";
            } elseif ($mouvement == '1') { // Depart
                $whereClause[] = "numero_depart != '--' AND numero_arrivee = '--'";
            }
        }

        // Filtering by month
        if (!empty($monthFilter)) {
            $whereClause[] = "MONTH(flight_date) = :month";
            $bindings['month'] = $monthFilter; // Month filter as integer (1-12)
        }

        // Filtering by date range
        if ($start_date && $end_date) {
            $whereClause[] = "flight_date BETWEEN :start_date AND :end_date";
            $bindings['start_date'] = $start_date;
            $bindings['end_date'] = $end_date;
        } elseif ($start_date) {
            $whereClause[] = "flight_date >= :start_date";
            $bindings['start_date'] = $start_date;
        } elseif ($end_date) {
            $whereClause[] = "flight_date <= :end_date";
            $bindings['end_date'] = $end_date;
        }

        // Filtering by seasonId
        if (!empty($seasonId)) {
            $whereClause[] = "saison_id = :seasonId";
            $bindings['seasonId'] = $seasonId;
        }

        // Combine the WHERE clauses
        $whereSQL = '';
        if (!empty($whereClause)) {
            $whereSQL = "WHERE " . implode(" AND ", $whereClause);
        }

        $query = "WITH FlightData AS (
            -- Start with vol_arrivee
            SELECT DISTINCT
                COALESCE(va.numero, '--') AS numero_arrivee,
                COALESCE(cc_arrivee.name, '--') AS arrivee,
                vc_arrive.nom AS arrivee_company,
                CASE
                    WHEN va.heure_arrive IS NOT NULL THEN CONCAT(SUBSTR(va.heure_arrive, 1, 2), 'h', SUBSTR(va.heure_arrive, 3, 2))
                    ELSE '--'
                END AS heure_arrive,
                COALESCE(vd.numero, '--') AS numero_depart,
                COALESCE(cc_depart.name, '--') AS depart,
                CASE
                    WHEN vd.heure_depart IS NOT NULL THEN CONCAT(SUBSTR(vd.heure_depart, 1, 2), 'h', SUBSTR(vd.heure_depart, 3, 2))
                    ELSE '--'
                END AS heure_depart,
                vc_depart.nom AS depart_company,
                a.equipement,
                a.capacite,
                na.name AS assist,
                CASE DAYNAME(COALESCE(va.date_vol, vd.date_vol))
                    WHEN 'Monday' THEN 'lundi'
                    WHEN 'Tuesday' THEN 'mardi'
                    WHEN 'Wednesday' THEN 'mercredi'
                    WHEN 'Thursday' THEN 'jeudi'
                    WHEN 'Friday' THEN 'vendredi'
                    WHEN 'Saturday' THEN 'samedi'
                    WHEN 'Sunday' THEN 'dimanche'
                    ELSE DAYNAME(COALESCE(va.date_vol, vd.date_vol))
                END AS jour_semaine,
                COALESCE(va.date_vol, vd.date_vol) AS flight_date,
                va.saison_id
            FROM vol_arrives va
            LEFT JOIN vol_freres vf ON va.numero = vf.numero_arrivee
            LEFT JOIN vol_departs vd ON vf.numero_depart = vd.numero
                AND vd.date_vol = CASE
                    WHEN vd.heure_depart BETWEEN '2310' AND '2359'
                    THEN DATE_ADD(va.date_vol, INTERVAL -1 DAY)
                    ELSE va.date_vol
                END
            LEFT JOIN city_codes cc_depart ON vd.destination = cc_depart.code
            LEFT JOIN city_codes cc_arrivee ON va.depart = cc_arrivee.code
            LEFT JOIN companies vc_arrive ON va.companie_id = vc_arrive.id
            LEFT JOIN companies vc_depart ON vd.companie_id = vc_depart.id
            LEFT JOIN avions a ON va.avion_id = a.id OR vd.avion_id = a.id
            LEFT JOIN name_assist na ON vc_arrive.nom = na.code OR vc_depart.nom = na.code

            UNION ALL

            -- Continue with vol_departs
            SELECT DISTINCT
                COALESCE(va.numero, '--') AS numero_arrivee,
                COALESCE(cc_arrivee.name, '--') AS arrivee,
                vc_arrive.nom AS arrivee_company,
                CASE
                    WHEN va.heure_arrive IS NOT NULL THEN CONCAT(SUBSTR(va.heure_arrive, 1, 2), 'h', SUBSTR(va.heure_arrive, 3, 2))
                    ELSE '--'
                END AS heure_arrive,
                COALESCE(vd.numero, '--') AS numero_depart,
                COALESCE(cc_depart.name, '--') AS depart,
                CASE
                    WHEN vd.heure_depart IS NOT NULL THEN CONCAT(SUBSTR(vd.heure_depart, 1, 2), 'h', SUBSTR(vd.heure_depart, 3, 2))
                    ELSE '--'
                END AS heure_depart,
                vc_depart.nom AS depart_company,
                a.equipement,
                a.capacite,
                na.name AS assist,
                CASE DAYNAME(COALESCE(va.date_vol, vd.date_vol))
                    WHEN 'Monday' THEN 'lundi'
                    WHEN 'Tuesday' THEN 'mardi'
                    WHEN 'Wednesday' THEN 'mercredi'
                    WHEN 'Thursday' THEN 'jeudi'
                    WHEN 'Friday' THEN 'vendredi'
                    WHEN 'Saturday' THEN 'samedi'
                    WHEN 'Sunday' THEN 'dimanche'
                    ELSE DAYNAME(COALESCE(va.date_vol, vd.date_vol))
                END AS jour_semaine,
                COALESCE(va.date_vol, vd.date_vol) AS flight_date,
                vd.saison_id
            FROM vol_departs vd
            LEFT JOIN vol_freres vf ON vd.numero = vf.numero_depart
            LEFT JOIN vol_arrives va ON vf.numero_arrivee = va.numero
                AND va.date_vol = CASE
                    WHEN va.heure_arrive BETWEEN '2310' AND '2359'
                    THEN DATE_ADD(vd.date_vol, INTERVAL -1 DAY)
                    ELSE vd.date_vol
                END
            LEFT JOIN city_codes cc_depart ON vd.destination = cc_depart.code
            LEFT JOIN city_codes cc_arrivee ON va.depart = cc_arrivee.code
            LEFT JOIN companies vc_arrive ON va.companie_id = vc_arrive.id
            LEFT JOIN companies vc_depart ON vd.companie_id = vc_depart.id
            LEFT JOIN avions a ON va.avion_id = a.id OR vd.avion_id = a.id
            LEFT JOIN name_assist na ON vc_arrive.nom = na.code OR vc_depart.nom = na.code
        )
        SELECT
            numero_arrivee,
            arrivee,
            arrivee_company,
            heure_arrive,
            numero_depart,
            depart,
            heure_depart,
            depart_company,
            equipement,
            capacite,
            assist,
            jour_semaine,
            MIN(flight_date) AS date_vol_min,
            MAX(flight_date) AS date_vol_max,
            saison_id
        FROM FlightData
        $whereSQL
        GROUP BY
            numero_arrivee,
            numero_depart,
            arrivee,
            arrivee_company,
            heure_arrive,
            depart,
            heure_depart,
            depart_company,
            equipement,
            capacite,
            assist,
            jour_semaine
        ORDER BY
            FIELD(jour_semaine, 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'),
            heure_arrive,
            heure_depart;";

        $vols = DB::select($query, $bindings);

        return DataTables::make($vols)->addColumn("numero", function ($vol) {
            $separator = $vol->numero_depart != "" && $vol->numero_arrivee != "" ? "/" : "";
            $companyInfo = "";

            if ($vol->depart_company && $vol->arrivee_company) {
                $companyInfo = $vol->arrivee_company . " " . $vol->numero_arrivee . $separator . " " . $vol->numero_depart;
            } elseif ($vol->arrivee_company) {
                $companyInfo = $vol->arrivee_company . " " . $vol->numero_arrivee;
            } elseif ($vol->depart_company) {
                $companyInfo = $vol->depart_company . " " . $vol->numero_depart;
            }

            return $companyInfo;
        })
        ->rawColumns(['numero'])
        ->toJson();
    }



    //new code saisonnier
    public function datatable_saisonnierNEW(Request $request)
    {
        $sql_arrive = "
        SELECT
            numero as numero,
            depart as depart,
            heure_arrive as heure_arrive,
            date_vol as date_vol,
            CASE DAYNAME(date_vol)
                WHEN 'Monday' THEN 'lundi'
                WHEN 'Tuesday' THEN 'mardi'
                WHEN 'Wednesday' THEN 'mercredi'
                WHEN 'Thursday' THEN 'jeudi'
                WHEN 'Friday' THEN 'vendredi'
                WHEN 'Saturday' THEN 'samedi'
                WHEN 'Sunday' THEN 'dimanche'
                ELSE DAYNAME(date_vol)
            END AS jour_semaine
        FROM vol_arrives
        ORDER BY date_vol
        ";

        $sql_depart = "
        SELECT
            numero as numero,
            destination as destination,
            heure_depart as heure_depart,
            date_vol as date_vol,
            CASE DAYNAME(date_vol)
                WHEN 'Monday' THEN 'lundi'
                WHEN 'Tuesday' THEN 'mardi'
                WHEN 'Wednesday' THEN 'mercredi'
                WHEN 'Thursday' THEN 'jeudi'
                WHEN 'Friday' THEN 'vendredi'
                WHEN 'Saturday' THEN 'samedi'
                WHEN 'Sunday' THEN 'dimanche'
                ELSE DAYNAME(date_vol)
            END AS jour_semaine
        FROM vol_departs
        ORDER BY date_vol
        ";

        $arrivs = DB::select($sql_arrive);
        $departs = DB::select($sql_depart);

        $resultat = [];

        foreach ($arrivs as $arrive) {
            $freres = DB::select("SELECT numero_depart FROM vol_freres WHERE numero_arrivee = ?", [$arrive->numero]);
            $freres = array_column($freres, 'numero_depart');

            $resultat[$arrive->jour_semaine][$arrive->date_vol][] = [
                "num_arrive" => $arrive->numero,
                "depart" => $arrive->depart,
                "heure_arrive" => $arrive->heure_arrive,
                "num_depart" => "--",
                "destination" => "--",
                "heure_depart" => "--",
                "freres" => $freres
            ];
        }

        foreach ($departs as $depart) {
            $found = false;
            if (isset($resultat[$depart->jour_semaine][$depart->date_vol])) {
                foreach ($resultat[$depart->jour_semaine][$depart->date_vol] as &$vol) {
                    if (in_array($depart->numero, $vol['freres'])) {
                        $vol['num_depart'] = $depart->numero;
                        $vol['destination'] = $depart->destination;
                        $vol['heure_depart'] = $depart->heure_depart;
                        $found = true;
                        break;
                    }
                }
            }

            if (!$found) {
                $resultat[$depart->jour_semaine][$depart->date_vol][] = [
                    'num_arrive' => "--",
                    'depart' => "--",
                    'heure_arrive' => "--",
                    'num_depart' => $depart->numero,
                    'destination' => $depart->destination,
                    'heure_depart' => $depart->heure_depart,
                    'freres' => []
                ];
            }
        }

        $flattenedResult = [];
        foreach ($resultat as $jour_semaine => $dates) {
            $min_date_vol = min(array_keys($dates));
            $max_date_vol = max(array_keys($dates));
            foreach ($dates as $date => $vols) {
                foreach ($vols as $vol) {
                    $flattenedResult[] = array_merge(
                        ['jour_semaine' => $jour_semaine],
                        ['min_date_vol' => $min_date_vol],
                        ['max_date_vol' => $max_date_vol],
                        $vol
                    );
                }
            }
        }

        return response()->json(['data' => $flattenedResult]);
    }
    public function datatable_saisonnier(Request $request)
    {
        $seasonId = $request->input('seasonId');
        // Query to fetch and combine data from both vol_departs and vol_arrives
        $flightData = DB::table('vol_departs as vd')
            ->selectRaw('
                COALESCE(va.numero, "") AS numero_arrivee,
                COALESCE(cc_arrivee.name, "-") AS arrivee,
                vc_arrive.nom AS arrivee_company,
                CASE
                    WHEN va.heure_arrive IS NOT NULL THEN CONCAT(SUBSTR(va.heure_arrive, 1, 2), "h", SUBSTR(va.heure_arrive, 3, 2))
                    ELSE "-"
                END AS heure_arrive,
                COALESCE(vd.numero, "") AS numero_depart,
                COALESCE(cc_depart.name, "-") AS depart,
                CASE
                    WHEN vd.heure_depart IS NOT NULL THEN CONCAT(SUBSTR(vd.heure_depart, 1, 2), "h", SUBSTR(vd.heure_depart, 3, 2))
                    ELSE "-"
                END AS heure_depart,
                vc_depart.nom AS depart_company,
                a.equipement,
                a.capacite,
                na.name AS assist,
                CASE DAYNAME(COALESCE(va.date_vol, vd.date_vol))
                    WHEN "Monday" THEN "Lundi"
                    WHEN "Tuesday" THEN "Mardi"
                    WHEN "Wednesday" THEN "Mercredi"
                    WHEN "Thursday" THEN "Jeudi"
                    WHEN "Friday" THEN "Vendredi"
                    WHEN "Saturday" THEN "Samedi"
                    WHEN "Sunday" THEN "Dimanche"
                END AS jour_semaine,
                COALESCE(va.date_vol, vd.date_vol) AS flight_date,
                vd.saison_id
            ')
            ->leftJoin('vol_freres as vf', 'vd.numero', '=', 'vf.numero_depart')
            ->leftJoin('vol_arrives as va', function ($join) {
                $join->on('vf.numero_arrivee', '=', 'va.numero')
                    ->whereRaw('va.date_vol = CASE
                                                WHEN va.heure_arrive BETWEEN "2310" AND "2359"
                                                THEN DATE_ADD(vd.date_vol, INTERVAL -1 DAY)
                                                ELSE vd.date_vol
                                            END');
            })
            ->leftJoin('city_codes as cc_depart', 'vd.destination', '=', 'cc_depart.code')
            ->leftJoin('city_codes as cc_arrivee', 'va.depart', '=', 'cc_arrivee.code')
            ->leftJoin('companies as vc_arrive', 'va.companie_id', '=', 'vc_arrive.id')
            ->leftJoin('companies as vc_depart', 'vd.companie_id', '=', 'vc_depart.id')
            ->leftJoin('avions as a', function ($join) {
                $join->on('va.avion_id', '=', 'a.id')
                    ->orOn('vd.avion_id', '=', 'a.id');
            })
            ->leftJoin('name_assist as na', function ($join) {
                $join->on('vc_arrive.nom', '=', 'na.code')
                    ->orOn('vc_depart.nom', '=', 'na.code');
            })
            ->whereRaw('vd.saison_id =?',[$seasonId]);

        $arrives = DB::table('vol_arrives as va')
            ->selectRaw('
                va.numero AS numero_arrivee,
                COALESCE(cc_arrivee.name, "-") AS arrivee,
                vc_arrive.nom AS arrivee_company,
                CASE
                    WHEN va.heure_arrive IS NOT NULL THEN CONCAT(SUBSTR(va.heure_arrive, 1, 2), "h", SUBSTR(va.heure_arrive, 3, 2))
                    ELSE "-"
                END AS heure_arrive,
                "" AS numero_depart,
                "" AS depart,
                "-" AS heure_depart,
                "" AS depart_company,
                a.equipement,
                a.capacite,
                na.name AS assist,
                CASE DAYNAME(va.date_vol)
                    WHEN "Monday" THEN "Lundi"
                    WHEN "Tuesday" THEN "Mardi"
                    WHEN "Wednesday" THEN "Mercredi"
                    WHEN "Thursday" THEN "Jeudi"
                    WHEN "Friday" THEN "Vendredi"
                    WHEN "Saturday" THEN "Samedi"
                    WHEN "Sunday" THEN "Dimanche"
                END AS jour_semaine,
                va.date_vol AS flight_date,
                va.saison_id
            ')
            ->leftJoin('city_codes as cc_arrivee', 'va.depart', '=', 'cc_arrivee.code')
            ->leftJoin('companies as vc_arrive', 'va.companie_id', '=', 'vc_arrive.id')
            ->leftJoin('avions as a', 'va.avion_id', '=', 'a.id')
            ->leftJoin('name_assist as na', 'vc_arrive.nom', '=', 'na.code')
            ->whereRaw('va.saison_id =?',[$seasonId]);

        // Combine the vol_departs and vol_arrives queries
        $combinedFlights = $flightData->unionAll($arrives)->get();

        // Group the combined flights
        $groupedFlights = collect($combinedFlights)
            ->groupBy(function ($item) {
                return $item->numero_arrivee . '|' . $item->numero_depart . '|' . $item->arrivee . '|' . $item->arrivee_company . '|' . $item->heure_arrive . '|' . $item->depart . '|' . $item->heure_depart . '|' . $item->depart_company . '|' . $item->equipement . '|' . $item->capacite . '|' . $item->assist . '|' . $item->jour_semaine;
            })
            ->map(function ($group) {
                return [
                    'numero_arrivee' => $group->first()->numero_arrivee,
                    'arrivee' => $group->first()->arrivee,
                    'arrivee_company' => $group->first()->arrivee_company,
                    'heure_arrive' => $group->first()->heure_arrive,
                    'numero_depart' => $group->first()->numero_depart,
                    'depart' => $group->first()->depart,
                    'heure_depart' => $group->first()->heure_depart,
                    'depart_company' => $group->first()->depart_company,
                    'equipement' => $group->first()->equipement,
                    'capacite' => $group->first()->capacite,
                    'assist' => $group->first()->assist,
                    'jour_semaine' => $group->first()->jour_semaine,
                    'date_vol_min' => $group->min('flight_date'),
                    'date_vol_max' => $group->max('flight_date'),
                    'saison_id' => $group->first()->saison_id,
                ];
        })
        ->sortBy(['jour_semaine', 'heure_arrive', 'heure_depart'])
        ->values()
        ->toArray();
        $FinalData=$this->removeDuplicateFlights($groupedFlights);
        // Return DataTables response
        return DataTables::of($FinalData)
            ->addColumn('numero', function ($vol) {
                $separator = $vol['numero_depart'] != "" && $vol['numero_arrivee'] != "" ? "/" : "";
                $companyInfo = "";

                if ($vol['depart_company'] && $vol['arrivee_company']) {
                    $companyInfo = $vol['arrivee_company'] . " " . $vol['numero_arrivee'] . $separator . " " . $vol['numero_depart'];
                } elseif ($vol['arrivee_company']) {
                    $companyInfo = $vol['arrivee_company'] . " " . $vol['numero_arrivee'];
                } elseif ($vol['depart_company']) {
                    $companyInfo = $vol['depart_company'] . " " . $vol['numero_depart'];
                }

                return $companyInfo;
            })
            ->rawColumns(['numero'])
            ->toJson();
    }
    private function removeDuplicateFlights($flights) {
        // Group the flights based on the unique key (all fields we want to avoid duplication on)
        $uniqueFlights = [];

        foreach ($flights as $flight) {
            $key = $flight['numero_arrivee'] . $flight['arrivee'] . $flight['arrivee_company'] . $flight['heure_arrive'] . $flight['equipement'] . $flight['capacite'] . $flight['assist'] . $flight['jour_semaine'] . $flight['saison_id'];

            // Check if this flight with the same key exists in the array
            if (isset($uniqueFlights[$key])) {
                // If the flight exists, update its departure details
                if (!empty($flight['numero_depart'])) {
                    $uniqueFlights[$key]['numero_depart'] = $flight['numero_depart'];
                    $uniqueFlights[$key]['depart'] = $flight['depart'];
                    $uniqueFlights[$key]['heure_depart'] = $flight['heure_depart'];
                    $uniqueFlights[$key]['depart_company'] = $flight['depart_company'];
                }
            } else {
                // Otherwise, add the flight as a new entry
                $uniqueFlights[$key] = $flight;
            }
        }

        // Return the unique flights array
        return array_values($uniqueFlights); // Reset array keys
    }

    public function datatable_saisonniersql(Request $request)
    {
        // Step 1: Combine vol_departs and vol_arrives with unionAll query
        $flightData = DB::table('vol_departs as vd')
            ->selectRaw('
                COALESCE(va.numero, "") AS numero_arrivee,
                COALESCE(cc_arrivee.name, "-") AS arrivee,
                vc_arrive.nom AS arrivee_company,
                CASE
                    WHEN va.heure_arrive IS NOT NULL THEN CONCAT(SUBSTR(va.heure_arrive, 1, 2), "h", SUBSTR(va.heure_arrive, 3, 2))
                    ELSE "-"
                END AS heure_arrive,
                COALESCE(vd.numero, "") AS numero_depart,
                COALESCE(cc_depart.name, "-") AS depart,
                CASE
                    WHEN vd.heure_depart IS NOT NULL THEN CONCAT(SUBSTR(vd.heure_depart, 1, 2), "h", SUBSTR(vd.heure_depart, 3, 2))
                    ELSE "-"
                END AS heure_depart,
                vc_depart.nom AS depart_company,
                a.equipement,
                a.capacite,
                na.name AS assist,
                DAYNAME(COALESCE(va.date_vol, vd.date_vol)) AS jour_semaine,
                COALESCE(va.date_vol, vd.date_vol) AS flight_date,
                WEEK(COALESCE(va.date_vol, vd.date_vol), 1) AS flight_week,  -- Set Monday as the first day of the week
                vd.saison_id
            ')
            ->leftJoin('vol_freres as vf', 'vd.numero', '=', 'vf.numero_depart')
            ->leftJoin('vol_arrives as va', function ($join) {
                $join->on('vf.numero_arrivee', '=', 'va.numero')
                    ->whereRaw('va.date_vol = CASE
                                                    WHEN va.heure_arrive BETWEEN "2310" AND "2359"
                                                    THEN DATE_ADD(vd.date_vol, INTERVAL -1 DAY)
                                                    ELSE vd.date_vol
                                                END');
            })
            ->leftJoin('city_codes as cc_depart', 'vd.destination', '=', 'cc_depart.code')
            ->leftJoin('city_codes as cc_arrivee', 'va.depart', '=', 'cc_arrivee.code')
            ->leftJoin('companies as vc_arrive', 'va.companie_id', '=', 'vc_arrive.id')
            ->leftJoin('companies as vc_depart', 'vd.companie_id', '=', 'vc_depart.id')
            ->leftJoin('avions as a', function ($join) {
                $join->on('va.avion_id', '=', 'a.id')
                    ->orOn('vd.avion_id', '=', 'a.id');
            })
            ->leftJoin('name_assist as na', function ($join) {
                $join->on('vc_arrive.nom', '=', 'na.code')
                    ->orOn('vc_depart.nom', '=', 'na.code');
            });

        $arrives = DB::table('vol_arrives as va')
            ->selectRaw('
                va.numero AS numero_arrivee,
                COALESCE(cc_arrivee.name, "-") AS arrivee,
                vc_arrive.nom AS arrivee_company,
                CASE
                    WHEN va.heure_arrive IS NOT NULL THEN CONCAT(SUBSTR(va.heure_arrive, 1, 2), "h", SUBSTR(va.heure_arrive, 3, 2))
                    ELSE "-"
                END AS heure_arrive,
                "" AS numero_depart,
                "" AS depart,
                "-" AS heure_depart,
                "" AS depart_company,
                a.equipement,
                a.capacite,
                na.name AS assist,
                DAYNAME(va.date_vol) AS jour_semaine,
                va.date_vol AS flight_date,
                WEEK(va.date_vol, 1) AS flight_week,  -- Set Monday as the first day of the week
                va.saison_id
            ')
            ->leftJoin('city_codes as cc_arrivee', 'va.depart', '=', 'cc_arrivee.code')
            ->leftJoin('companies as vc_arrive', 'va.companie_id', '=', 'vc_arrive.id')
            ->leftJoin('avions as a', 'va.avion_id', '=', 'a.id')
            ->leftJoin('name_assist as na', 'vc_arrive.nom', '=', 'na.code');

        // Step 2: Combine the flight data
        $combinedFlights = $flightData->unionAll($arrives)->get();

        // Step 3: Group by week and count the number of flights per week
        $flightsByWeek = $combinedFlights->groupBy('flight_week')
            ->map(function ($week) {
                return [
                    'flight_week' => $week->first()->flight_week,
                    'flight_count' => $week->count(),
                    'flights' => $week,
                ];
            });

        // Step 4: Identify the busiest week
        $busiestWeek = $flightsByWeek->sortByDesc('flight_count')->slice(1)->first();

        // Step 5: Process and return the flights in the busiest week via DataTables
        $sortedFlights = $busiestWeek['flights']->sortBy(function ($flight) {
            $daysMap = [
                'Lundi' => 1,
                'Mardi' => 2,
                'Mercredi' => 3,
                'Jeudi' => 4,
                'Vendredi' => 5,
                'Samedi' => 6,
                'Dimanche' => 7
            ];
            return $daysMap[$flight->jour_semaine] ?? 8; // 8 for unknown days
        });

        return DataTables::of($sortedFlights)
            ->addColumn('date_vol', function ($vol) {
                return $vol->flight_date; // Display date_vol
            })
            ->addColumn('numero', function ($vol) {
                return $vol->depart_company . " " . $vol->numero_arrivee .
                    ($vol->numero_depart != "" ? " / " . $vol->depart_company . " " . $vol->numero_depart : ""); // Concatenated flight numbers
            })
            ->addColumn('equipement', function ($vol) {
                return $vol->equipement; // Display equipement
            })
            ->addColumn('capacite', function ($vol) {
                return $vol->capacite; // Display capacite
            })
            ->addColumn('assist', function ($vol) {
                return $vol->assist; // Display assist
            })
            ->addColumn('arrivee', function ($vol) {
                return $vol->arrivee; // Display arrivee
            })
            ->addColumn('heure_arrive', function ($vol) {
                return $vol->heure_arrive; // Display heure_arrive
            })
            ->addColumn('depart', function ($vol) {
                return $vol->depart; // Display depart
            })
            ->addColumn('heure_depart', function ($vol) {
                return $vol->heure_depart; // Display heure_depart
            })
            ->rawColumns(['numero'])
            ->make(true);
    }

    public function datatable_saisonnier2(Request $request)
    {
        $dayFilter = $request->input('day_of_week');
        $mouvementFilter = $request->input('mouvement');
        $monthFilter = $request->input('month');

        // SQL Queries
        $sql_arrive = "
            SELECT
                va.numero AS numero,
                va.depart AS depart,
                va.heure_arrive AS heure_arrive,
                va.date_vol AS date_vol,
                va.companie_id AS companie_id,
                a.equipement AS equipement,
                a.capacite AS capacite,
                c.nom AS company_name_arrive,
                ass.name AS assist_info,
                MIN(va.date_vol) OVER (PARTITION BY va.numero, DAYOFWEEK(va.date_vol), va.heure_arrive) AS periode_min,
                MAX(va.date_vol) OVER (PARTITION BY va.numero, DAYOFWEEK(va.date_vol), va.heure_arrive) AS periode_max
            FROM vol_arrives va
            JOIN avions a ON va.avion_id = a.id
            JOIN companies c ON va.companie_id = c.id
            LEFT JOIN name_assist ass ON c.nom = ass.code
            ORDER BY va.date_vol,va.heure_arrive
        ";

        $sql_depart = "
            SELECT
                vd.numero AS numero,
                vd.destination AS destination,
                vd.heure_depart AS heure_depart,
                vd.date_vol AS date_vol,
                vd.companie_id AS companie_id,
                a.equipement AS equipement,
                a.capacite AS capacite,
                c.nom AS company_name_depart,
                ass.name AS assist_info,
                MIN(vd.date_vol) OVER (PARTITION BY vd.numero, DAYOFWEEK(vd.date_vol), vd.heure_depart) AS periode_min,
                MAX(vd.date_vol) OVER (PARTITION BY vd.numero, DAYOFWEEK(vd.date_vol), vd.heure_depart) AS periode_max
            FROM vol_departs vd
            JOIN avions a ON vd.avion_id = a.id
            JOIN companies c ON vd.companie_id = c.id
            LEFT JOIN name_assist ass ON c.nom = ass.code
            ORDER BY vd.date_vol,vd.heure_depart
        ";

        // Fetch flight data
        $arrives =DB::select($sql_arrive);
        $departs =DB::select($sql_depart);

        // Fetch city codes
        $cityCodes = DB::table('city_codes')->get()->keyBy('code');

        // Initialize result array
        $resultat = [];

        // Process arrivals
        foreach ($arrives as $arrive) {
            $adjusted_date_vol = $arrive->heure_arrive >= '2310' && $arrive->heure_arrive <= '2359'
                ? date('Y-m-d', strtotime($arrive->date_vol . ' 1 day'))
                : $arrive->date_vol;

            $dayOfWeek = Carbon::createFromFormat('Y-m-d', $arrive->date_vol)->locale('fr_FR')->isoFormat('dddd');

            // Fetch departure flights matching this arrival
            $freres = DB::table('vol_freres')
                ->where('numero_arrivee', $arrive->numero)
                ->pluck('numero_depart')
                ->toArray();

            // Avoid adding duplicates
            if (!isset($resultat[$dayOfWeek][$arrive->numero])) {
                $resultat[$dayOfWeek][$arrive->numero] = [
                    "num_arrive" => $arrive->numero,
                    "depart" => $cityCodes->get($arrive->depart)->name ?? 'Unknown',
                    "heure_arrive" => substr($arrive->heure_arrive, 0, 2) . 'h' . substr($arrive->heure_arrive, 2, 2),
                    "num_depart" => "--",
                    "destination" => "--",
                    "heure_depart" => "--",
                    "equipement" => $arrive->equipement,
                    "capacite" => $arrive->capacite,
                    "company_name_arrive" => $arrive->company_name_arrive,
                    "company_name_depart" => null,
                    "date_vols" => [$adjusted_date_vol],
                    "assist_info" => $arrive->assist_info,
                    "freres" => $freres,
                    "date_vol_min" => $arrive->periode_min,  // Add periode_min
                    "date_vol_max" => $arrive->periode_max,  // Add periode_max
                ];
            }
        }

        // Process departures
        foreach ($departs as $depart) {
            $dayOfWeek = Carbon::createFromFormat('Y-m-d', $depart->date_vol)->locale('fr_FR')->isoFormat('dddd');

            if (isset($resultat[$dayOfWeek])) {
                foreach ($resultat[$dayOfWeek] as &$vol) {
                    if (in_array($depart->numero, $vol['freres'])) {
                        if ($vol['num_depart'] === "--") {
                            $vol['num_depart'] = $depart->numero;
                            $vol['destination'] = $cityCodes->get($depart->destination)->name ?? 'Unknown';
                            $vol['heure_depart'] = substr($depart->heure_depart, 0, 2) . 'h' . substr($depart->heure_depart, 2, 2);
                            $vol['company_name_depart'] = $depart->company_name_depart;
                            $vol['date_vols'][] = $depart->date_vol;
                            $vol['date_vol_min'] = $vol['date_vol_min'] ?? $depart->periode_min;
                            $vol['date_vol_max'] = $vol['date_vol_max'] ?? $depart->periode_max;
                            break;
                        }

                    }
                }
            } else {
                // If no corresponding arrival flight found, create a new entry
                $resultat[$dayOfWeek][$depart->numero] = [
                    'num_arrive' => "--",
                    'depart' => "--",
                    'heure_arrive' => "--",
                    'num_depart' => $depart->numero,
                    'destination' => $cityCodes->get($depart->destination)->name ?? 'Unknown',
                    'heure_depart' => substr($depart->heure_depart, 0, 2) . 'h' . substr($depart->heure_depart, 2, 2),
                    'equipement' => $depart->equipement,
                    'capacite' => $depart->capacite,
                    'company_name_arrive' => null,
                    'company_name_depart' => $depart->company_name_depart,
                    'date_vols' => [$depart->date_vol],
                    'freres' => [],
                    'assist_info' => $depart->assist_info,
                    "date_vol_min" => $depart->periode_min,  // Add periode_min
                    "date_vol_max" => $depart->periode_max,  // Add periode_max
                ];
            }
        }

        // Flatten results and calculate min/max dates
        $flattenedResult = [];
        foreach ($resultat as $dayOfWeek => $vols) {
            foreach ($vols as $vol) {


                $companyInfo = $vol['company_name_arrive'] ? $vol['company_name_arrive'] . ' ' . $vol['num_arrive'] : '';
                $companyInfo .= $vol['company_name_depart'] ? ($vol['company_name_arrive'] ? '/' : '') . $vol['num_depart'] : '';

                $flattenedResult[] = array_merge(['day_of_week' => $dayOfWeek], $vol, [
                    'company_info' => $companyInfo,

                ]);
            }
        }

        // Apply filters
        if ($dayFilter) {
            $flattenedResult = array_filter($flattenedResult, function ($vol) use ($dayFilter) {
                return $vol['day_of_week'] === $dayFilter;
            });
        }
        if ($mouvementFilter) {
            $flattenedResult = array_filter($flattenedResult, function ($vol) use ($mouvementFilter) {
                if ($mouvementFilter == '-1') {
                    // Show only arrivals
                    return $vol['num_arrive'] !== '--' && $vol['num_depart'] === '--';
                } elseif ($mouvementFilter == '1') {
                    // Show only departures
                    return $vol['num_depart'] !== '--' && $vol['num_arrive'] === '--';
                }
                // Show both arrivals and departures
                return true;
            });
        }
        if ($monthFilter) {
            $flattenedResult = array_filter($flattenedResult, function ($vol) use ($monthFilter) {
                $volMonth = Carbon::parse($vol['date_vols'])->format('n'); // Get month as a number (1-12)
                return $volMonth == $monthFilter;
            });
        }

        // Return DataTables JSON response
        return DataTables::of($flattenedResult)
            ->addColumn('jour_semaine', function ($vol) {
                return $vol['day_of_week'];
            })
            ->addColumn('equipement', function ($vol) {
                return $vol['equipement'];
            })
            ->addColumn('capacite', function ($vol) {
                return $vol['capacite'];
            })
            ->addColumn('arrivee', function ($vol) {
                return $vol['depart']; // City name for departure
            })
            ->addColumn('heure_arrive', function ($vol) {
                return $vol['heure_arrive'];
            })
            ->addColumn('depart', function ($vol) {
                return $vol['destination']; // City name for destination
            })
            ->addColumn('heure_depart', function ($vol) {
                return $vol['heure_depart'];
            })
            ->addColumn('numero', function ($vol) {
                return $vol['company_info'];
            })

            ->addColumn('assist', function ($vol) {
                return $vol['assist_info']; // Add assist info
            })
            ->rawColumns(['jour_semaine', 'numero', 'equipement', 'capacite', 'arrivee', 'heure_arrive', 'depart', 'heure_depart', 'assist'])
            ->toJson();
    }

    public function datatable_saisonnierJJ(Request $request)
    {
        $dayFilter = $request->input('day_of_week');
        $mouvementFilter = $request->input('mouvement');
        $monthFilter = $request->input('month');
        $startDate = $request->input('du');
        $endDate = $request->input('au');

        // SQL Queries
        $sql_arrive = "
            SELECT DISTINCT
                va.numero AS numero,
                va.depart AS depart,
                va.heure_arrive AS heure_arrive,
                va.date_vol AS date_vol,
                va.companie_id AS companie_id,
                a.equipement AS equipement,
                a.capacite AS capacite,
                c.nom AS company_name_arrive,
                ass.name AS assist_info,
                MIN(va.date_vol) OVER (PARTITION BY va.numero, DAYOFWEEK(va.date_vol),va.heure_arrive) AS periode_min,
                MAX(va.date_vol) OVER (PARTITION BY va.numero, DAYOFWEEK(va.date_vol),va.heure_arrive) AS periode_max
            FROM vol_arrives va
            JOIN avions a ON va.avion_id = a.id
            JOIN companies c ON va.companie_id = c.id
            LEFT JOIN name_assist ass ON c.nom = ass.code
            ORDER BY va.date_vol, va.heure_arrive
        ";

        $sql_depart = "
            SELECT DISTINCT
                vd.numero AS numero,
                vd.destination AS destination,
                vd.heure_depart AS heure_depart,
                vd.date_vol AS date_vol,
                vd.companie_id AS companie_id,
                a.equipement AS equipement,
                a.capacite AS capacite,
                c.nom AS company_name_depart,
                ass.name AS assist_info,
                MIN(vd.date_vol) OVER (PARTITION BY vd.numero, DAYOFWEEK(vd.date_vol),vd.heure_depart) AS periode_min,
                MAX(vd.date_vol) OVER (PARTITION BY vd.numero, DAYOFWEEK(vd.date_vol),vd.heure_depart) AS periode_max
            FROM vol_departs vd
            JOIN avions a ON vd.avion_id = a.id
            JOIN companies c ON vd.companie_id = c.id
            LEFT JOIN name_assist ass ON c.nom = ass.code
            ORDER BY vd.date_vol, vd.heure_depart
        ";

        // Fetch flight data
        $arrives = DB::select($sql_arrive);
        $departs = DB::select($sql_depart);

        // Fetch city codes
        $cityCodes = DB::table('city_codes')->get()->keyBy('code');

        // Initialize result array
        $resultat = [];

        // Process arrivals
        foreach ($arrives as $arrive) {
            $adjusted_date_vol = $arrive->heure_arrive >= '2310' && $arrive->heure_arrive <= '2359'
                ? date('Y-m-d', strtotime($arrive->date_vol . ' +1 day'))
                : $arrive->date_vol;

            $dayOfWeek = Carbon::createFromFormat('Y-m-d', $adjusted_date_vol)->locale('fr_FR')->isoFormat('dddd');

            // Fetch departure flights matching this arrival
            $freres = DB::table('vol_freres')
                ->where('numero_arrivee', $arrive->numero)
                ->pluck('numero_depart')
                ->toArray();

            // Avoid adding duplicates
            if (!isset($resultat[$dayOfWeek][$arrive->numero])) {
                $resultat[$dayOfWeek][$arrive->numero] = [
                    "num_arrive" => $arrive->numero,
                    "depart" => $cityCodes->get($arrive->depart)->name ?? 'Unknown',
                    "heure_arrive" => substr($arrive->heure_arrive, 0, 2) . 'h' . substr($arrive->heure_arrive, 2, 2),
                    "num_depart" => "--",
                    "destination" => "--",
                    "heure_depart" => "--",
                    "equipement" => $arrive->equipement,
                    "capacite" => $arrive->capacite,
                    "company_name_arrive" => $arrive->company_name_arrive,
                    "company_name_depart" => null,
                    "date_vols" => [$adjusted_date_vol],
                    "assist_info" => $arrive->assist_info,
                    "freres" => $freres,
                    "periode_min" => $arrive->periode_min,  // Add periode_min
                    "periode_max" => $arrive->periode_max,  // Add periode_max
                ];
            }
        }

        // Process departures
        foreach ($departs as $depart) {
            $dayOfWeek = Carbon::createFromFormat('Y-m-d', $depart->date_vol)->locale('fr_FR')->isoFormat('dddd');

            if (isset($resultat[$dayOfWeek])) {
                foreach ($resultat[$dayOfWeek] as &$vol) {
                    if (in_array($depart->numero, $vol['freres'])) {
                        if ($vol['num_depart'] === "--") {
                            $vol['num_depart'] = $depart->numero;
                            $vol['destination'] = $cityCodes->get($depart->destination)->name ?? 'Unknown';
                            $vol['heure_depart'] = substr($depart->heure_depart, 0, 2) . 'h' . substr($depart->heure_depart, 2, 2);
                            $vol['company_name_depart'] = $depart->company_name_depart;
                            $vol['date_vols'][] = $depart->date_vol;
                            // Update periode_min and periode_max for departures
                            $vol['periode_min'] = $vol['periode_min'] ?? $depart->periode_min;
                            $vol['periode_max'] = $vol['periode_max'] ?? $depart->periode_max;
                        }
                        break;
                    }
                }
            } else {
                // If no corresponding arrival flight found, create a new entry
                $resultat[$dayOfWeek][$depart->numero] = [
                    'num_arrive' => "--",
                    'depart' => "--",
                    'heure_arrive' => "--",
                    'num_depart' => $depart->numero,
                    'destination' => $cityCodes->get($depart->destination)->name ?? 'Unknown',
                    'heure_depart' => substr($depart->heure_depart, 0, 2) . 'h' . substr($depart->heure_depart, 2, 2),
                    'equipement' => $depart->equipement,
                    'capacite' => $depart->capacite,
                    'company_name_arrive' => null,
                    'company_name_depart' => $depart->company_name_depart,
                    'date_vols' => [$depart->date_vol],
                    'freres' => [],
                    'assist_info' => $depart->assist_info,
                    "periode_min" => $depart->periode_min,  // Add periode_min
                    "periode_max" => $depart->periode_max,  // Add periode_max
                ];
            }
        }

        // Flatten results
        $flattenedResult = [];
        foreach ($resultat as $dayOfWeek => $vols) {
            foreach ($vols as $vol) {
                $companyInfo = $vol['company_name_arrive'] ? $vol['company_name_arrive'] . ' ' . $vol['num_arrive'] : '';
                $companyInfo .= $vol['company_name_depart'] ? ($vol['company_name_arrive'] ? '/' : '') . $vol['num_depart'] : '';
                $flattenedResult[] = array_merge(['day_of_week' => $dayOfWeek], $vol, [
                    'company_info' => $companyInfo,
                ]);

            }
        }

        // Apply date range filter
        if ($startDate && $endDate) {
            $flattenedResult = array_filter($flattenedResult, function ($vol) use ($startDate, $endDate) {
                foreach ($vol['date_vols'] as $date_vol) {
                    if ($date_vol >= $startDate && $date_vol <= $endDate) {
                        return true;
                    }
                }
                return false;
            });
        }

        // Apply other filters
        if ($dayFilter) {
            $flattenedResult = array_filter($flattenedResult, function ($vol) use ($dayFilter) {
                return $vol['day_of_week'] === $dayFilter;
            });
        }
        if ($mouvementFilter) {
            $flattenedResult = array_filter($flattenedResult, function ($vol) use ($mouvementFilter) {
                if ($mouvementFilter == '-1') {
                    // Show only arrivals
                    return $vol['num_arrive'] !== '--' && $vol['num_depart'] === '--';
                } elseif ($mouvementFilter == '1') {
                    // Show only departures
                    return $vol['num_depart'] !== '--' && $vol['num_arrive'] === '--';
                }
                // Show both arrivals and departures
                return true;
            });
        }
        if ($monthFilter) {
            $flattenedResult = array_filter($flattenedResult, function ($vol) use ($monthFilter) {
                $volMonth = Carbon::parse($vol['periode_min'])->format('n'); // Get month as a number (1-12)
                return $volMonth == $monthFilter;
            });
        }

        // Return DataTables JSON response
        return DataTables::of($flattenedResult)
            ->addColumn('date_vol', function ($row) {
                return $row['date_vols'] ? implode(', ', $row['date_vols']) : '';
            })
            ->make(true);
    }

    private function isValidDate($date)
    {
        return (bool)strtotime($date);
    }

    // Helper function to calculate the max date based on the provided criteria
    private function calculateMaxDate($date_vols, $dayFilter, $heure_depart)
    {
        $max_date_vol = null;

        // Convert date_vols to Carbon instances for easy date manipulation
        $dateInstances = array_map(function($date) {
            return Carbon::createFromFormat('Y-m-d', $date);
        }, $date_vols);

        // Sort the dates
        sort($dateInstances);

        // Get the current date
        $today = Carbon::now();

        // Find the next occurrence of the specified day
        if ($dayFilter) {
            $targetDay = Carbon::createFromFormat('l', $dayFilter);
            $nextOccurrence = $today->copy()->next($targetDay->dayOfWeek);

            // Look for the next available date that matches the flight number and time
            foreach ($dateInstances as $date) {
                if ($date->isSameDay($nextOccurrence) && $heure_depart) {
                    // If the flight is available at the same time
                    $max_date_vol = $nextOccurrence->format('Y-m-d');
                    break; // Found a matching date
                }
                // Move to the next occurrence of the specified day
                $nextOccurrence->addWeek();
            }
        }

        return $max_date_vol;
    }

    // public function datatable_somainezz(Request $request)
    // {

    //     $dayFilter = $request->input('day_of_week');
    //     $mouvementFilter = $request->input('mouvement');

    //     $highestLoadWeek = DB::table('vol_arrives')
    //         ->select(DB::raw("DATE_FORMAT(date_vol, '%Y-%u') AS week_year"), DB::raw('COUNT(*) AS flight_count'))
    //         ->groupBy(DB::raw("DATE_FORMAT(date_vol, '%Y-%u')"))
    //         ->orderBy('flight_count', 'DESC')
    //         ->orderBy('week_year', 'DESC')
    //         ->limit(1)
    //         ->first();

    //     $weekYear = $highestLoadWeek->week_year;

    //     $sql_arrive = "
    //         SELECT DISTINCT
    //             va.numero AS numero,
    //             va.depart AS depart,
    //             va.heure_arrive AS heure_arrive,
    //             va.date_vol AS date_vol,
    //             va.companie_id AS companie_id,
    //             a.equipement AS equipement,
    //             a.capacite AS capacite,
    //             c.nom AS company_name_arrive,
    //             ass.name AS assist_info
    //         FROM vol_arrives va
    //         JOIN avions a ON va.avion_id = a.id
    //         JOIN companies c ON va.companie_id = c.id
    //         LEFT JOIN name_assist ass ON c.nom = ass.code
    //         WHERE DATE_FORMAT(date_vol, '%Y/%u') = ?
    //         ORDER BY va.date_vol,va.heure_arrive
    //     ";

    //     $sql_depart = "
    //         SELECT DISTINCT
    //             vd.numero AS numero,
    //             vd.destination AS destination,
    //             vd.heure_depart AS heure_depart,
    //             vd.date_vol AS date_vol,
    //             vd.companie_id AS companie_id,
    //             a.equipement AS equipement,
    //             a.capacite AS capacite,
    //             c.nom AS company_name_depart,
    //             ass.name AS assist_info
    //         FROM vol_departs vd
    //         JOIN avions a ON vd.avion_id = a.id
    //         JOIN companies c ON vd.companie_id = c.id
    //         LEFT JOIN name_assist ass ON c.nom = ass.code
    //         WHERE DATE_FORMAT(date_vol, '%Y/%u') = ?
    //         ORDER BY vd.date_vol,vd.heure_depart
    //     ";

    //     $arrives = DB::select($sql_arrive, [$weekYear]);
    //     $departs = DB::select($sql_depart, [$weekYear]);

    //     $cityCodes = DB::table('city_codes')->get()->keyBy('code');

    //     $resultat = [];

    //     foreach ($arrives as $arrive) {
    //         $adjusted_date_vol = $arrive->heure_arrive >= '2310' && $arrive->heure_arrive <= '2359'
    //             ? date('d/m/Y', strtotime($arrive->date_vol . ' 1 day'))
    //             : $arrive->date_vol;

    //         $dayOfWeek = Carbon::createFromFormat('Y-m-d', $adjusted_date_vol)->locale('fr_FR')->isoFormat('dddd');

    //         $freres = DB::table('vol_freres')
    //             ->where('numero_arrivee', $arrive->numero)
    //             ->pluck('numero_depart')
    //             ->toArray();

    //         if (!isset($resultat[$dayOfWeek][$arrive->numero])) {
    //             $resultat[$dayOfWeek][$arrive->numero] = [
    //                 "num_arrive" => $arrive->numero,
    //                 "depart" => $cityCodes->get($arrive->depart)->name ?? 'Unknown',
    //                 "heure_arrive" => substr($arrive->heure_arrive, 0, 2) . 'h' . substr($arrive->heure_arrive, 2, 2),
    //                 "num_depart" => "--",
    //                 "destination" => "--",
    //                 "heure_depart" => "--",
    //                 "equipement" => $arrive->equipement,
    //                 "capacite" => $arrive->capacite,
    //                 "company_name_arrive" => $arrive->company_name_arrive,
    //                 "company_name_depart" => null,
    //                 "date_vols" => [$adjusted_date_vol],
    //                 "assist_info" => $arrive->assist_info,
    //                 "freres" => $freres,
    //             ];
    //         }
    //     }

    //     foreach ($departs as $depart) {
    //         $dayOfWeek = Carbon::createFromFormat('Y-m-d', $depart->date_vol)->locale('fr_FR')->isoFormat('dddd');

    //         if (isset($resultat[$dayOfWeek])) {
    //             foreach ($resultat[$dayOfWeek] as &$vol) {
    //                 if (in_array($depart->numero, $vol['freres'])) {
    //                     if ($vol['num_depart'] === "--") {
    //                         $vol['num_depart'] = $depart->numero;
    //                         $vol['destination'] = $cityCodes->get($depart->destination)->name ?? 'Unknown';
    //                         $vol['heure_depart'] = substr($depart->heure_depart, 0, 2) . 'h' . substr($depart->heure_depart, 2, 2);
    //                         $vol['company_name_depart'] = $depart->company_name_depart;
    //                         $vol['date_vols'][] = $depart->date_vol;
    //                     }
    //                     break;
    //                 }
    //             }
    //         } else {
    //             $resultat[$dayOfWeek][$depart->numero] = [
    //                 'num_arrive' => "--",
    //                 'depart' => "--",
    //                 'heure_arrive' => "--",
    //                 'num_depart' => $depart->numero,
    //                 'destination' => $cityCodes->get($depart->destination)->name ?? 'Unknown',
    //                 'heure_depart' => substr($depart->heure_depart, 0, 2) . 'h' . substr($depart->heure_depart, 2, 2),
    //                 'equipement' => $depart->equipement,
    //                 'capacite' => $depart->capacite,
    //                 'company_name_arrive' => null,
    //                 'company_name_depart' => $depart->company_name_depart,
    //                 'date_vols' => [$depart->date_vol],
    //                 'freres' => [],
    //                 'assist_info' => $depart->assist_info,
    //             ];
    //         }
    //     }

    //     $flattenedResult = [];
    //     foreach ($resultat as $dayOfWeek => $vols) {
    //         foreach ($vols as $vol) {
    //             $companyInfo = $vol['company_name_arrive'] ? $vol['company_name_arrive'] . ' ' . $vol['num_arrive'] : '';
    //             $companyInfo .= $vol['company_name_depart'] ? ($vol['company_name_arrive'] ? '/' : '') . $vol['num_depart'] : '';

    //             $flattenedResult[] = array_merge(['day_of_week' => $dayOfWeek], $vol, [
    //                 'company_info' => $companyInfo,
    //             ]);
    //         }
    //     }

    //     if ($dayFilter) {
    //         $flattenedResult = array_filter($flattenedResult, function ($vol) use ($dayFilter) {
    //             return $vol['day_of_week'] === $dayFilter;
    //         });
    //     }
    //     if ($mouvementFilter) {
    //         $flattenedResult = array_filter($flattenedResult, function ($vol) use ($mouvementFilter) {
    //             if ($mouvementFilter == '-1') {
    //                 return $vol['num_arrive'] !== '--' && $vol['num_depart'] === '--';
    //             } elseif ($mouvementFilter == '1') {
    //                 return $vol['num_depart'] !== '--' && $vol['num_arrive'] === '--';
    //             }
    //             return true;
    //         });
    //     }

    //     return DataTables::of($flattenedResult)
    //         ->addColumn('date_vol', function ($vol) {
    //             return $vol['day_of_week'];
    //         })
    //         ->addColumn('numero', function ($vol) {
    //             return $vol['num_arrive'] !== '--' ? $vol['num_arrive'] : $vol['num_depart'];
    //         })
    //         ->addColumn('equipement', function ($vol) {
    //             return $vol['equipement'];
    //         })
    //         ->addColumn('capacite', function ($vol) {
    //             return $vol['capacite'];
    //         })
    //         ->addColumn('arrivee', function ($vol) {
    //             return $vol['depart'];
    //         })
    //         ->addColumn('heure_arrive', function ($vol) {
    //             return $vol['heure_arrive'];
    //         })
    //         ->addColumn('depart', function ($vol) {
    //             return $vol['destination'];
    //         })
    //         ->addColumn('heure_depart', function ($vol) {
    //             return $vol['heure_depart'];
    //         })
    //         ->addColumn('company_info', function ($vol) {
    //             return $vol['company_info'];
    //         })
    //         ->addColumn('assist_info', function ($vol) {
    //             return $vol['assist_info'];
    //         })
    //         ->rawColumns(['date_vol', 'numero', 'equipement', 'capacite', 'arrivee', 'heure_arrive', 'depart', 'heure_depart', 'company_info', 'assist_info'])
    //         ->toJson();
    // }

    public function datatable_somainez(Request $request)
    {
        $selectedDate = $request->input('selectedDate');
        $movment = $request->input('movment');
        $selectedSeason = $request->input('selectedSeason');
        // Fetch necessary data
        $highestLoadWeek = DB::table('vol_arrives')
            ->select(DB::raw("DATE_FORMAT(date_vol, '%Y-%u') AS week_year"), DB::raw('COUNT(*) AS flight_count'))
            ->groupBy(DB::raw("DATE_FORMAT(date_vol, '%Y-%u')"))
            ->orderBy('flight_count', 'DESC')
            ->orderBy('week_year', 'DESC')
            ->limit(1)
            ->first();
        $weekYear = $highestLoadWeek->week_year;

        $sql_arrive = "
            SELECT
                numero as numero,
                depart as depart,
                heure_arrive as heure_arrive,
                date_vol as date_vol,
                avion_id,
                companie_id
            FROM vol_arrives
            WHERE DATE_FORMAT(date_vol, '%Y-%u') = ? ".
            ($selectedDate ? "AND date_vol = ? " : "") .
            ($selectedSeason ? "AND saison_id = ? " : "") ."
            ORDER BY date_vol, heure_arrive
        ";

        $sql_depart = "
            SELECT
                numero as numero,
                destination as destination,
                heure_depart as heure_depart,
                date_vol as date_vol,
                avion_id,
                companie_id
            FROM vol_departs
            WHERE DATE_FORMAT(date_vol, '%Y-%u') = ? ".
            ($selectedDate ? "AND date_vol = ? " : "") .
            ($selectedSeason ? "AND saison_id = ? " : "") ."
            ORDER BY date_vol, heure_depart
        ";

        $params = [$weekYear];
        if ($selectedDate) {
            $params[] = $selectedDate;
        }
        if ($selectedSeason) {
            $params[] = $selectedSeason; // Add the season to the parameters
        }

        $arrivs = DB::select($sql_arrive, $params);
        $departs =DB::select($sql_depart, $params);
        if ($movment == "-1") { // Arrivée
            $departs = [];
        } elseif ($movment == "1") { // Depart
            $arrivs = [];
        }
        $volFreres = DB::table('vol_freres')->get()->keyBy('numero_arrivee');

        $cityCodes = DB::table('city_codes')->get()->keyBy('code');
        $companies = DB::table('companies')->get()->keyBy('id');
        $avions = DB::table('avions')->get()->keyBy('id');
        $assistInfos = DB::table('name_assist')->get()->keyBy('code');

        $resultat = [];

        // Process arrivals
        foreach ($arrivs as $arrive) {
            $vf = isset($volFreres[$arrive->numero]) ? $volFreres[$arrive->numero] : null;
            $depart = $vf ? collect($departs)->firstWhere('numero', $vf->numero_depart) : null;

            $adjustedDate = $depart ? ($arrive->heure_arrive >= '2310' && $arrive->heure_arrive <= '2359' ? date('Y-m-d', strtotime($depart->date_vol . ' -1 day')) : $depart->date_vol) : null;
            if ($adjustedDate && $arrive->date_vol != $adjustedDate) {
                continue;
            }

            $ccArrivee = $cityCodes->get($arrive->depart);
            $vcArrive = isset($arrive->companie_id) ? $companies->get($arrive->companie_id) : null;
            $ccDepart = $depart ? $cityCodes->get($depart->destination) : null;
            $vcDepart = $depart && isset($depart->companie_id) ? $companies->get($depart->companie_id) : null;
            $avion = $avions->get($arrive->avion_id) ?: ($depart ? $avions->get($depart->avion_id) : null);
            $assistInfo = $vcArrive ? ($assistInfos->get($vcArrive->nom)->name ?? null) : null;

            $separator = $depart ? '/' : '';
            $companyInfo = "";

            if ($vcArrive && $vcDepart) {
                $companyInfo = $vcArrive->nom . " " . $arrive->numero . $separator . " " . $depart->numero;
            } elseif ($vcArrive) {
                $companyInfo = $vcArrive->nom . " " . $arrive->numero;
            } elseif ($vcDepart) {
                $companyInfo = $vcDepart->nom . " " . $depart->numero;
            }

            $resultat[] = [
                'date_vol' => $arrive->date_vol,
                'numero' => $companyInfo,
                'equipement' => $avion->equipement ?? null,
                'capacite' => $avion->capacite ?? null,
                'arrivee' => $ccArrivee->name ?? '-',
                'heure_arrive' => $arrive->heure_arrive ? substr($arrive->heure_arrive, 0, 2) . 'h' . substr($arrive->heure_arrive, 2, 2) : '-',
                'depart' => $ccDepart->name ?? '-',
                'heure_depart' => $depart ? ($depart->heure_depart ? substr($depart->heure_depart, 0, 2) . 'h' . substr($depart->heure_depart, 2, 2) : '-') : '-',
                'assist' => $assistInfo
            ];
        }

        // Process departures
        foreach ($departs as $depart) {
            $vf = isset($volFreres[$depart->numero]) ? $volFreres[$depart->numero] : null;
            $arrive = $vf ? collect($arrivs)->firstWhere('numero', $vf->numero_arrivee) : null;

            $adjustedDate = $arrive ? ($arrive->heure_arrive >= '2310' && $arrive->heure_arrive <= '2359' ? date('Y-m-d', strtotime($depart->date_vol . ' -1 day')) : $depart->date_vol) : null;
            if ($adjustedDate && $arrive->date_vol != $adjustedDate) {
                continue;
            }

            $ccArrivee = $arrive ? $cityCodes->get($arrive->depart) : null;
            $vcArrive = $arrive && isset($arrive->companie_id) ? $companies->get($arrive->companie_id) : null;
            $ccDepart = $cityCodes->get($depart->destination);
            $vcDepart = isset($depart->companie_id) ? $companies->get($depart->companie_id) : null;
            $avion = $arrive ? $avions->get($arrive->avion_id) : $avions->get($depart->avion_id);
            $assistInfo = $vcDepart ? ($assistInfos->get($vcDepart->nom)->name ?? null) : null;

            $separator = $arrive ? '/' : '';
            $companyInfo = "";

            if ($vcArrive && $vcDepart) {
                $companyInfo = $vcArrive->nom . " " . $arrive->numero . $separator . " " . $depart->numero;
            } elseif ($vcArrive) {
                $companyInfo = $vcArrive->nom . " " . $arrive->numero;
            } elseif ($vcDepart) {
                $companyInfo = $vcDepart->nom . " " . $depart->numero;
            }

            $resultat[] = [
                'date_vol' => $depart->date_vol,
                'numero' => $companyInfo,
                'equipement' => $avion->equipement ?? null,
                'capacite' => $avion->capacite ?? null,
                'arrivee' => $ccArrivee ? $ccArrivee->name : '-',
                'heure_arrive' => $arrive ? ($arrive->heure_arrive ? substr($arrive->heure_arrive, 0, 2) . 'h' . substr($arrive->heure_arrive, 2, 2) : '-') : '-',
                'depart' => $ccDepart->name ?? '-',
                'heure_depart' => $depart->heure_depart ? substr($depart->heure_depart, 0, 2) . 'h' . substr($depart->heure_depart, 2, 2) : '-',
                'assist' => $assistInfo
            ];
        }

        // Sort flights by date_vol
        usort($resultat, function ($a, $b) {
            return strtotime($a['date_vol']) - strtotime($b['date_vol']);
        });

        // Use DataTables to transform the result into the desired format
        return DataTables::of($resultat)
            ->addColumn('numero', function ($vol) {
                return $vol['numero'];
            })
            ->rawColumns(['numero'])
            ->toJson();
    }

    public function datatable_somaine(Request $request)
    {
       $selectedSeason = $request->input('selectedSeason');
    //    $selectedSeason = 1  ;
        // Base query for departure flights
        $flightData = DB::table('vol_departs as vd')
            ->selectRaw('
                COALESCE(va.numero, "") AS numero_arrivee,
                COALESCE(cc_arrivee.name, "-") AS arrivee,
                vc_arrive.nom AS arrivee_company,
                CASE
                    WHEN va.heure_arrive IS NOT NULL THEN CONCAT(SUBSTR(va.heure_arrive, 1, 2), "h", SUBSTR(va.heure_arrive, 3, 2))
                    ELSE "-"
                END AS heure_arrive,
                COALESCE(vd.numero, "") AS numero_depart,
                COALESCE(cc_depart.name, "-") AS depart,
                CASE
                    WHEN vd.heure_depart IS NOT NULL THEN CONCAT(SUBSTR(vd.heure_depart, 1, 2), "h", SUBSTR(vd.heure_depart, 3, 2))
                    ELSE "-"
                END AS heure_depart,
                vc_depart.nom AS depart_company,
                a.equipement,
                a.capacite,
                na.name AS assist,
                DAYNAME(COALESCE(va.date_vol, vd.date_vol)) AS date_vol,
                COALESCE(va.date_vol, vd.date_vol) AS flight_date,
                vd.saison_id
            ')
            ->leftJoin('vol_freres as vf', 'vd.numero', '=', 'vf.numero_depart')
            ->leftJoin('vol_arrives as va', function ($join) {
                $join->on('vf.numero_arrivee', '=', 'va.numero')
                    ->whereRaw('va.date_vol = CASE
                                                WHEN va.heure_arrive BETWEEN "2310" AND "2359"
                                                THEN DATE_ADD(vd.date_vol, INTERVAL -1 DAY)
                                                ELSE vd.date_vol
                                            END');
            })
            ->leftJoin('city_codes as cc_depart', 'vd.destination', '=', 'cc_depart.code')
            ->leftJoin('city_codes as cc_arrivee', 'va.depart', '=', 'cc_arrivee.code')
            ->leftJoin('companies as vc_arrive', 'va.companie_id', '=', 'vc_arrive.id')
            ->leftJoin('companies as vc_depart', 'vd.companie_id', '=', 'vc_depart.id')
            ->leftJoin('avions as a', function ($join) {
                $join->on('va.avion_id', '=', 'a.id')
                    ->orOn('vd.avion_id', '=', 'a.id');
            })
            ->leftJoin('name_assist as na', function ($join) {
                $join->on('vc_arrive.nom', '=', 'na.code')
                    ->orOn('vc_depart.nom', '=', 'na.code');

            })->whereRaw('vd.saison_id =?',[$selectedSeason]);



        // Base query for arrival flights
        $arrives = DB::table('vol_arrives as va')
            ->selectRaw('
                va.numero AS numero_arrivee,
                COALESCE(cc_arrivee.name, "-") AS arrivee,
                vc_arrive.nom AS arrivee_company,
                CASE
                    WHEN va.heure_arrive IS NOT NULL THEN CONCAT(SUBSTR(va.heure_arrive, 1, 2), "h", SUBSTR(va.heure_arrive, 3, 2))
                    ELSE "-"
                END AS heure_arrive,
                "" AS numero_depart,
                "" AS depart,
                "-" AS heure_depart,
                "" AS depart_company,
                a.equipement,
                a.capacite,
                na.name AS assist,
                DAYNAME(va.date_vol) AS date_vol,
                va.date_vol AS flight_date,
                va.saison_id
            ')
            ->leftJoin('city_codes as cc_arrivee', 'va.depart', '=', 'cc_arrivee.code')
            ->leftJoin('companies as vc_arrive', 'va.companie_id', '=', 'vc_arrive.id')
            ->leftJoin('avions as a', 'va.avion_id', '=', 'a.id')
            ->leftJoin('name_assist as na', 'vc_arrive.nom', '=', 'na.code')
            ->whereRaw('va.saison_id =?',[$selectedSeason]);



        // Combine the vol_departs and vol_arrives queries
        $combinedFlights = $flightData->unionAll($arrives)->get();

        // Group the combined flights by week and count them
        $weeklyFlightCounts = collect($combinedFlights)->groupBy(function ($item) {
            return \Carbon\Carbon::parse($item->flight_date)->format('W-Y'); // Group by week number and year
        })->map(function ($group) {
            return [
                'week' => $group->first()->flight_date, // Store the date of the first flight in the group
                'count' => $group->count(), // Count the number of flights in the week
                'flights' => $group, // Store all the flights in the group
                'week_format' => \Carbon\Carbon::parse($group->first()->flight_date)->format('W-Y')
            ];
        });

        // Sort by the week format (W-Y)
        $sortedWeeklyFlightCounts = $weeklyFlightCounts->sortByDesc(function ($item) {
            return \Carbon\Carbon::parse($item['week'])->format('W-Y'); // Sort by week number and year
        })->sortByDesc('count'); // Then sort by count

        // Get the busiest week
        // echo"<pre>";
        // print_r($sortedWeeklyFlightCounts);
        // echo"</pre>";
        // die;
        // If you want to convert it back to a collection

        $busiestWeek = $sortedWeeklyFlightCounts->first(); // Get the first result, which is the busiest week
        // Now you can get the busiest week or any other data as needed
        // $busiestWeek = $sortedWeeklyFlightCounts->sortByDesc('count')->first(); // Get the busiest week



        // Get flights for the busiest week
        $finalFlights = $busiestWeek ? $busiestWeek['flights'] : collect();

        $finalresult = $this->removeDuplicateFlightsSomaine($finalFlights);


        // Return DataTables response
        return DataTables::of($finalresult)
            ->addColumn('numero', function ($vol) {
                $separator = $vol->numero_depart != "" && $vol->numero_arrivee != "" ? "/" : "";
                $companyInfo = "";

                if ($vol->depart_company && $vol->arrivee_company) {
                    $companyInfo = $vol->arrivee_company . " " . $vol->numero_arrivee . $separator . " " . $vol->numero_depart;
                } elseif ($vol->arrivee_company) {
                    $companyInfo = $vol->arrivee_company . " " . $vol->numero_arrivee;
                } elseif ($vol->depart_company) {
                    $companyInfo = $vol->depart_company . " " . $vol->numero_depart;
                }

                return $companyInfo;
            })
            ->rawColumns(['numero'])
            ->toJson();
    }




    public function datatable_somainesql()
    {
        DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY',''))");

        $sql = "
        WITH FlightData AS (
            SELECT DISTINCT
                COALESCE(va.numero, '--') AS numero_arrivee,
                COALESCE(cc_arrivee.name, '--') AS arrivee,
                vc_arrive.nom AS arrivee_company,
                COALESCE(CONCAT(SUBSTR(va.heure_arrive, 1, 2), 'h', SUBSTR(va.heure_arrive, 3, 2)), '--') AS heure_arrive,
                COALESCE(vd.numero, '--') AS numero_depart,
                COALESCE(cc_depart.name, '--') AS depart,
                COALESCE(CONCAT(SUBSTR(vd.heure_depart, 1, 2), 'h', SUBSTR(vd.heure_depart, 3, 2)), '--') AS heure_depart,
                vc_depart.nom AS depart_company,
                a.equipement,
                a.capacite,
                na.name AS assist,
                DAYNAME(COALESCE(va.date_vol, vd.date_vol)) AS jour_semaine,
                COALESCE(va.date_vol, vd.date_vol) AS flight_date,
                WEEK(COALESCE(va.date_vol, vd.date_vol), 1) AS week_number
            FROM
                vol_departs vd
            LEFT JOIN
                vol_freres vf ON vd.numero = vf.numero_depart
            LEFT JOIN
                vol_arrives va ON vf.numero_arrivee = va.numero AND va.date_vol = vd.date_vol
            LEFT JOIN
                city_codes cc_depart ON vd.destination = cc_depart.code
            LEFT JOIN
                city_codes cc_arrivee ON va.depart = cc_arrivee.code
            LEFT JOIN
                companies vc_arrive ON va.companie_id = vc_arrive.id
            LEFT JOIN
                companies vc_depart ON vd.companie_id = vc_depart.id
            LEFT JOIN
                avions a ON va.avion_id = a.id OR vd.avion_id = a.id
            LEFT JOIN
                name_assist na ON vc_arrive.nom = na.code OR vc_depart.nom = na.code

            UNION ALL

            SELECT DISTINCT
                COALESCE(va.numero, '--') AS numero_arrivee,
                COALESCE(cc_arrivee.name, '--') AS arrivee,
                vc_arrive.nom AS arrivee_company,
                COALESCE(CONCAT(SUBSTR(va.heure_arrive, 1, 2), 'h', SUBSTR(va.heure_arrive, 3, 2)), '--') AS heure_arrive,
                COALESCE(vd.numero, '--') AS numero_depart,
                COALESCE(cc_depart.name, '--') AS depart,
                COALESCE(CONCAT(SUBSTR(vd.heure_depart, 1, 2), 'h', SUBSTR(vd.heure_depart, 3, 2)), '--') AS heure_depart,
                vc_depart.nom AS depart_company,
                a.equipement,
                a.capacite,
                na.name AS assist,
                DAYNAME(COALESCE(va.date_vol, vd.date_vol)) AS jour_semaine,
                COALESCE(va.date_vol, vd.date_vol) AS flight_date,
                WEEK(COALESCE(va.date_vol, vd.date_vol), 1) AS week_number
            FROM
                vol_arrives va
            LEFT JOIN
                vol_freres vf ON va.numero = vf.numero_arrivee
            LEFT JOIN
                vol_departs vd ON vf.numero_depart = vd.numero AND va.date_vol = CASE
                    WHEN va.heure_arrive BETWEEN '2310' AND '2359' THEN DATE_ADD(vd.date_vol, INTERVAL -1 DAY)
                    ELSE vd.date_vol
                END
            LEFT JOIN
                city_codes cc_depart ON vd.destination = cc_depart.code
            LEFT JOIN
                city_codes cc_arrivee ON va.depart = cc_arrivee.code
            LEFT JOIN
                companies vc_arrive ON va.companie_id = vc_arrive.id
            LEFT JOIN
                companies vc_depart ON vd.companie_id = vc_depart.id
            LEFT JOIN
                avions a ON va.avion_id = a.id OR vd.avion_id = a.id
            LEFT JOIN
                name_assist na ON vc_arrive.nom = na.code OR vc_depart.nom = na.code
        ),
        WeekFlightCount AS (
            SELECT week_number, COUNT(*) AS flight_count
            FROM FlightData
            GROUP BY week_number
            ORDER BY flight_count DESC
            LIMIT 1 OFFSET 1
        )
        SELECT
            flight_date AS date_vol,
            numero_arrivee,
            arrivee,
            arrivee_company,
            heure_arrive,
            numero_depart,
            depart,
            heure_depart,
            depart_company,
            equipement,
            capacite,
            assist
        FROM
            FlightData
        WHERE
            week_number = (SELECT week_number FROM WeekFlightCount)
        ORDER BY
            FIELD(jour_semaine, 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'),
            heure_arrive,
            heure_depart
        ";

        $vols = DB::select($sql);

        return DataTables::make($vols)->addColumn("numero", function ($vol) {
            $separator = $vol->numero_depart != "" && $vol->numero_arrivee != "" ? "/" : "";
            return trim(implode(" ", array_filter([
                $vol->arrivee_company ? $vol->arrivee_company . " " . $vol->numero_arrivee : null,
                $vol->depart_company ? $vol->depart_company . " " . $vol->numero_depart : null
            ]))) ?: '';
        })
        ->rawColumns(['numero'])
        ->toJson();
    }


    // new code
    public function datatable_somaine1()
    {
                // Get the current date
            $currentDate = now()->format('Y-m-d');

            // Calculate the date one week from now
            $oneWeekLater = now()->addWeek()->format('Y-m-d');

            $sql_arrive = "
            SELECT
                numero as numero,
                depart as depart,
                heure_arrive as heure_arrive,
                date_vol as date_vol
            FROM vol_arrives
            WHERE date_vol BETWEEN ? AND ?
            ORDER BY date_vol
            ";

            $sql_depart = "
            SELECT
                numero as numero,
                destination as destination,
                heure_depart as heure_depart,
                date_vol as date_vol
            FROM vol_departs
            WHERE date_vol BETWEEN ? AND ?
            ORDER BY date_vol
            ";

            $arrivs = DB::select($sql_arrive, [$currentDate, $oneWeekLater]);
            $departs = DB::select($sql_depart, [$currentDate, $oneWeekLater]);

            $resultat = [];
            $arriveCount = 0; // Counter for arrivals
            $departCount = 0; // Counter for departures

            foreach ($arrivs as $arrive) {
                $freres = DB::select("SELECT numero_depart FROM vol_freres WHERE numero_arrivee = ?", [$arrive->numero]);
                $freres = array_column($freres, 'numero_depart');

                $resultat[$arrive->date_vol][] = [
                    "num_arrive" => $arrive->numero,
                    "depart" => $arrive->depart,
                    "heure_arrive" => $arrive->heure_arrive,
                    "num_depart" => "--",
                    "destination" => "--",
                    "heure_depart" => "--",
                    "freres" => $freres
                ];

                $arriveCount++; // Increment arrival counter
            }

            foreach ($departs as $depart) {
                $found = false;
                if (isset($resultat[$depart->date_vol])) {
                    foreach ($resultat[$depart->date_vol] as &$vol) {
                        if (in_array($depart->numero, $vol['freres'])) {
                            $vol['num_depart'] = $depart->numero;
                            $vol['destination'] = $depart->destination;
                            $vol['heure_depart'] = $depart->heure_depart;
                            $found = true;
                            break;
                        }
                    }
                }

                if (!$found) {
                    $resultat[$depart->date_vol][] = [
                        'num_arrive' => "--",
                        'depart' => "--",
                        'heure_arrive' => "--",
                        'num_depart' => $depart->numero,
                        'destination' => $depart->destination,
                        'heure_depart' => $depart->heure_depart,
                        'freres' => []
                    ];
                }

                $departCount++; // Increment departure counter
            }

            $flattenedResult = [];
            foreach ($resultat as $date => $vols) {
                foreach ($vols as $vol) {
                    $flattenedResult[] = array_merge(['date' => $date], $vol);
                }
            }

            return response()->json([
                'data' => $flattenedResult,
                'counts' => [
                    'arrivals' => $arriveCount,
                    'departures' => $departCount,
                ]
            ]);
    }
    public function datatable_somaineQQ(Request $request)
    {
        // Capture the selected date from the request
        $selectedDate = $request->input('selectedDate');

        $flightData = DB::table('vol_departs as vd')
            ->select([
                DB::raw('COALESCE(va.numero, \'--\') AS numero_arrivee'),
                DB::raw('COALESCE(cc_arrivee.name, \'--\') AS arrivee'),
                'vc_arrive.nom AS arrivee_company',
                DB::raw('CASE WHEN va.heure_arrive IS NOT NULL THEN CONCAT(SUBSTR(va.heure_arrive, 1, 2), \'h\', SUBSTR(va.heure_arrive, 3, 2)) ELSE \'--\' END AS heure_arrive'),
                DB::raw('COALESCE(vd.numero, \'--\') AS numero_depart'),
                DB::raw('COALESCE(cc_depart.name, \'--\') AS depart'),
                DB::raw('CASE WHEN vd.heure_depart IS NOT NULL THEN CONCAT(SUBSTR(vd.heure_depart, 1, 2), \'h\', SUBSTR(vd.heure_depart, 3, 2)) ELSE \'--\' END AS heure_depart'),
                'vc_depart.nom AS depart_company',
                'a.equipement',
                'a.capacite',
                'na.name AS assist',
                DB::raw('CASE DAYNAME(COALESCE(va.date_vol, vd.date_vol))
                            WHEN \'Monday\' THEN \'lundi\'
                            WHEN \'Tuesday\' THEN \'mardi\'
                            WHEN \'Wednesday\' THEN \'mercredi\'
                            WHEN \'Thursday\' THEN \'jeudi\'
                            WHEN \'Friday\' THEN \'vendredi\'
                            WHEN \'Saturday\' THEN \'samedi\'
                            WHEN \'Sunday\' THEN \'dimanche\'
                            ELSE DAYNAME(COALESCE(va.date_vol, vd.date_vol))
                        END AS jour_semaine'),
                DB::raw('COALESCE(va.date_vol, vd.date_vol) AS flight_date'),
                DB::raw('WEEK(COALESCE(va.date_vol, vd.date_vol), 1) AS week_number')
            ])
            ->leftJoin('vol_freres as vf', 'vd.numero', '=', 'vf.numero_depart')
            ->leftJoin('vol_arrives as va', function ($join) {
                $join->on('vf.numero_arrivee', '=', 'va.numero')
                    ->where('va.date_vol', DB::raw("CASE WHEN va.heure_arrive BETWEEN '2310' AND '2359' THEN DATE_ADD(vd.date_vol, INTERVAL -1 DAY) ELSE vd.date_vol END"));
            })
            ->leftJoin('city_codes as cc_depart', 'vd.destination', '=', 'cc_depart.code')
            ->leftJoin('city_codes as cc_arrivee', 'va.depart', '=', 'cc_arrivee.code')
            ->leftJoin('companies as vc_arrive', 'va.companie_id', '=', 'vc_arrive.id')
            ->leftJoin('companies as vc_depart', 'vd.companie_id', '=', 'vc_depart.id')
            ->leftJoin('avions as a', function ($join) {
                $join->on('va.avion_id', '=', 'a.id')
                    ->orOn('vd.avion_id', '=', 'a.id');
            })
            ->leftJoin('name_assist as na', function ($join) {
                $join->on('vc_arrive.nom', '=', 'na.code')
                    ->orOn('vc_depart.nom', '=', 'na.code');
            });

        // Filter by the selected date if provided
        if ($selectedDate) {
            $flightData->where(DB::raw('COALESCE(va.date_vol, vd.date_vol)'), $selectedDate);
        }

        // The rest of your logic...
        $weekFlightCount = DB::table(DB::raw("({$flightData->toSql()}) as FlightData"))
            ->select('week_number', DB::raw('COUNT(*) AS flight_count'))
            ->groupBy('week_number')
            ->orderBy('flight_count', 'DESC')
            ->limit(1)
            ->offset(0)
            ->pluck('week_number')
            ->first();

        $result = DB::table(DB::raw("({$flightData->toSql()}) as FlightData"))
            ->select([
                'flight_date AS date_vol',
                'numero_arrivee',
                'arrivee',
                'arrivee_company',
                'heure_arrive',
                'numero_depart',
                'depart',
                'heure_depart',
                'depart_company',
                'equipement',
                'capacite',
                'assist'
            ])
            ->where('week_number', $weekFlightCount)
            ->groupBy([
                'flight_date',
                'numero_arrivee',
                'numero_depart',
                'arrivee',
                'arrivee_company',
                'heure_arrive',
                'depart',
                'heure_depart',
                'depart_company',
                'equipement',
                'capacite',
                'assist'
            ])
            ->orderByRaw("FIELD(jour_semaine, 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'), heure_arrive, heure_depart")
            ->get()
            ->toArray();

        $finalresult = $this->removeDuplicateFlightsSomaine($result);

        // echo "<pre>";
        // print_r($finalresult);
        // echo "</pre>";
        return DataTables::make($finalresult)->addColumn("numero", function ($vol) {
            $separator = $vol->numero_depart != "" && $vol->numero_arrivee != "" ? "/" : "";
            $companyInfo = "";

            if ($vol->depart_company && $vol->arrivee_company) {
                $companyInfo = $vol->arrivee_company . " " . $vol->numero_arrivee . $separator . " " . $vol->numero_depart;
            } elseif ($vol->arrivee_company) {
                $companyInfo = $vol->arrivee_company . " " . $vol->numero_arrivee;
            } elseif ($vol->depart_company) {
                $companyInfo = $vol->depart_company . " " . $vol->numero_depart;
            }

            return $companyInfo;
        })
        ->rawColumns(['numero'])
        ->toJson();
    }

    private function removeDuplicateFlightsSomaine($flights) {
        // Group the flights based on the unique key (all fields we want to avoid duplication on)
        $uniqueFlights = [];

        foreach ($flights as $flight) {
            $key = $flight->numero_arrivee . $flight->arrivee . $flight->arrivee_company .
                   $flight->heure_arrive . $flight->equipement . $flight->capacite .
                   $flight->assist . $flight->date_vol;
                //    . $flight->saison_id
            // Check if this flight with the same key exists in the array
            if (isset($uniqueFlights[$key])) {
                // If the flight exists, update its departure details
                if (!empty($flight->numero_depart)) {
                    $uniqueFlights[$key]->numero_depart = $flight->numero_depart;
                    $uniqueFlights[$key]->depart = $flight->depart;
                    $uniqueFlights[$key]->heure_depart = $flight->heure_depart;
                    $uniqueFlights[$key]->depart_company = $flight->depart_company;
                }
            } else {
                // Otherwise, add the flight as a new entry
                $uniqueFlights[$key] = $flight;
            }
        }

        // Return the unique flights array
        return array_values($uniqueFlights); // Reset array keys
    }






    public function Export_Data(Request $request){

        $programme = $request->input('programme');
        $exportClass = null;
        $fileName = '';
        $selectedSeason = $request->input('saison');
        // Determine which export class and filename to use based on the selected programme
        switch ($programme) {
            case '1':
                $exportClass = new VolsExport($selectedSeason);
                $fileName = 'programme saisonnier.xlsx';
                break;
            case '2':
                $exportClass = new Vols_somaine_export($selectedSeason);
                $fileName = 'programme semaine.xlsx';
                break;
            case '3':
                $exportClass = new Vols_Heurs_Export($selectedSeason);
                $fileName = 'Semaine type des vols par creneau horaire.xlsx';
                break;
            default:
                return back()->withErrors(['programme' => 'Invalid programme selected.']);
        }

        // Return the Excel download with the appropriate export class and filename
        return Excel::download($exportClass, $fileName);


    }
    public function export()
    {
        return Excel::download(new VolsExport, 'Programme saisonnier.xlsx');
    }
    public function export_heurs()
    {
        return Excel::download(new Vols_Heurs_Export, 'Semaine type des vols par creneau horaire.xlsx');
    }
    public function export_somaine()
    {
        return Excel::download(new Vols_somaine_export, 'Programme somaine.xlsx');
    }
}
