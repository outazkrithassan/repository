<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;
class Vols_somaine_export implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents, WithCustomStartCell
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $annee;
    protected $min_date_vol = null;
    protected $max_date_vol = null;
    protected $selectedSeason;

    public function __construct($selectedSeason)
    {
        $this->selectedSeason = $selectedSeason;
    }
    public function collection()
    {
        $selectedSeason = $this->selectedSeason;
        // Start timing the execution
        $startTime = microtime(true);
        // dd($selectedSeason);
        // die;
        // Set SQL mode to avoid ONLY_FULL_GROUP_BY issues
        DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY',''))");

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

        })->where('vd.saison_id',$selectedSeason);



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
            ->where('va.saison_id',$selectedSeason);



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
            ];
        });

        // Find the second busiest week
        $busiestWeek = $weeklyFlightCounts
            ->sortByDesc('count') // Sort by the number of flights in descending order
            ->skip(1) // Skip the first result (the busiest week)
            ->take(1) // Limit the result to only 1 (get the second busiest week)
            ->first(); // Get the first item, which is the second busiest week's flights

        // Get flights for the busiest week
        $finalFlights = $busiestWeek ? $busiestWeek['flights'] : collect();
        $finalresult = $this->removeDuplicateFlightsSomaine($finalFlights);
        $flightanne = $finalFlights->pluck('saison')->all();

        // Check if there are any seasons available and set $this->annee
        $this->annee = !empty($flightanne) ? $flightanne[0] : 'N/A';
        $flightDates = $finalFlights->pluck('flight_date')->all();
        if (!empty($flightDates)) {
            $this->min_date_vol = Carbon::createFromFormat('Y-m-d', min($flightDates))->locale('fr_FR')->isoFormat('DD');
            $this->max_date_vol = Carbon::createFromFormat('Y-m-d', max($flightDates))->locale('fr_FR')->isoFormat('DD MMMM YYYY');
        } else {
            $this->min_date_vol = null;
            $this->max_date_vol = null;
        }


        // Process final results
        $retour = [];
        foreach ($finalresult as $val) {
            $separator = $val->numero_arrivee != "" && $val->numero_depart != "" ? "/" : "";
            $company = $val->arrivee_company ? $val->arrivee_company : $val->depart_company;
            $numero = $company . ' ' . $val->numero_arrivee . $separator . $val->numero_depart;

            $retour[] = [
                'date_vol' => $val->flight_date,
                'numero' => $numero,
                'type_app' => $val->equipement,
                'capacite' => $val->capacite,
                'assistant' => $val->assist,
                'arrivee' => $val->arrivee,
                'heure_arrive' => $val->heure_arrive,
                'depart' => $val->depart,
                'heure_depart' => $val->heure_depart,
            ];
        }
        // $finalresult = $this->removeDuplicateFlightsSomaine($retour);

        // Sort the result by 'date_vol'
        usort($retour, function ($a, $b) {
            return strtotime($a['date_vol']) - strtotime($b['date_vol']);
        });
        // echo "<pre>";
        // print_r($retour);
        // echo "</pre>";
        // die;
        // Output the result


        // Calculate and log execution time
        $executionTime = microtime(true) - $startTime;
        Log::info('Execution Time:', ['time' => $executionTime]);

        // Convert the results to a collection
        return collect($retour);
    }
    // public function collection()
    // {
    //     $selectedSeason = $this->selectedSeason;
    //     // Start timing the execution
    //     $startTime = microtime(true);
    //     // dd($selectedSeason);
    //     // die;
    //     // Set SQL mode to avoid ONLY_FULL_GROUP_BY issues
    //     DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY',''))");

    //     $flightData = DB::table('vol_arrives as va')
    //         ->selectRaw('
    //             COALESCE(va.numero, "") AS  numero_arrivee,
    //             COALESCE(cc_arrivee.name, "-") AS  arrivee,
    //             vc_arrive.nom AS arrivee_company,
    //             CASE
    //                 WHEN va.heure_arrive IS NOT NULL THEN CONCAT(SUBSTR(va.heure_arrive, 1, 2), "h", SUBSTR(va.heure_arrive, 3, 2))
    //                 ELSE "-"
    //             END AS  heure_arrive,
    //             COALESCE(vd.numero, "") AS numero_depart,
    //             COALESCE(cc_depart.name, "-") AS depart,
    //             CASE
    //                 WHEN vd.heure_depart IS NOT NULL THEN CONCAT(SUBSTR(vd.heure_depart, 1, 2), "h", SUBSTR(vd.heure_depart, 3, 2))
    //                 ELSE "-"
    //             END AS heure_depart,
    //             vc_depart.nom AS depart_company,
    //             a.equipement,
    //             a.capacite,
    //             na.name AS assist,
    //             DAYNAME(COALESCE(va.date_vol, vd.date_vol)) AS date_vol,
    //             COALESCE(va.date_vol, vd.date_vol) AS flight_date,
    //             va.saison_id,
    //             sa.annee AS saison
    //         ')
    //         ->leftJoin('vol_freres as vf', 'va.numero', '=', 'vf.numero_arrivee')
    //         ->leftJoin('vol_departs as vd', function ($join) {
    //             $join->on('vf.numero_depart', '=', 'vd.numero')
    //                 ->whereRaw('va.date_vol = CASE
    //                                             WHEN va.heure_arrive BETWEEN "2310" AND "2359"
    //                                             THEN DATE_ADD(vd.date_vol, INTERVAL -1 DAY)
    //                                             ELSE vd.date_vol
    //                                         END');
    //         })
    //         ->leftJoin('city_codes as cc_depart', 'vd.destination', '=', 'cc_depart.code')
    //         ->leftJoin('city_codes as cc_arrivee', 'va.depart', '=', 'cc_arrivee.code')
    //         ->leftJoin('companies as vc_arrive', 'va.companie_id', '=', 'vc_arrive.id')
    //         ->leftJoin('companies as vc_depart', 'vd.companie_id', '=', 'vc_depart.id')
    //         ->leftJoin('avions as a', function ($join) {
    //             $join->on('va.avion_id', '=', 'a.id')
    //                 ->orOn('vd.avion_id', '=', 'a.id');
    //         })
    //         ->leftJoin('name_assist as na', function ($join) {
    //             $join->on('vc_arrive.nom', '=', 'na.code')
    //                 ->orOn('vc_depart.nom', '=', 'na.code');
    //         })
    //         ->leftJoin('saisons as sa', function ($join) {
    //             $join->on('va.saison_id', '=', 'sa.id')
    //                 ->orOn('vd.saison_id', '=', 'sa.id');
    //         })
    //         ->where('va.saison_id', $selectedSeason)
    //         ->orderBy('va.date_vol', 'asc');

    //     // Base query for arrival flights
    //     $departs = DB::table('vol_departs as vd')
    //         ->selectRaw('
    //             vd.numero AS numero_depart,
    //             COALESCE(cc_depart.name, "-") AS depart,
    //             vc_depart.nom AS depart_company,
    //             CASE
    //                 WHEN vd.heure_depart IS NOT NULL THEN CONCAT(SUBSTR(vd.heure_depart, 1, 2), "h", SUBSTR(vd.heure_depart, 3, 2))
    //                 ELSE "-"
    //             END AS heure_depart,
    //             "" AS numero_arrivee,
    //             "" AS arrivee,
    //             "" AS heure_arrive,
    //             "" AS arrivee_company,
    //             a.equipement,
    //             a.capacite,
    //             na.name AS assist,
    //             DAYNAME(vd.date_vol) AS date_vol,
    //             vd.date_vol AS flight_date,
    //             vd.saison_id,
    //             sa.annee AS saison
    //         ')
    //         ->leftJoin('city_codes as cc_depart', 'vd.destination', '=', 'cc_depart.code')
    //         ->leftJoin('companies as vc_depart', 'vd.companie_id', '=', 'vc_depart.id')
    //         ->leftJoin('avions as a', 'vd.avion_id', '=', 'a.id')
    //         ->leftJoin('name_assist as na', 'vc_depart.nom', '=', 'na.code')
    //         ->leftJoin('saisons as sa', 'vd.saison_id', '=', 'sa.id')
    //         ->where('vd.saison_id', $selectedSeason)
    //         ->orderBy('vd.date_vol', 'asc');

    //     // Combine the vol_departs and vol_arrives queries
    //     $combinedFlights = $flightData->unionAll($departs)->get();

    //     // Group the combined flights by week and count them
    //     $weeklyFlightCounts = collect($combinedFlights)
    //         ->groupBy(function ($item) {
    //             return \Carbon\Carbon::parse($item->flight_date)->format('W-Y'); // Group by week number and year
    //         })
    //         ->map(function ($group) {
    //             return [
    //                 'week' => $group->first()->flight_date, // Store the date of the first flight in the group
    //                 'count' => $group->count(), // Count the number of flights in the week
    //                 'flights' => $group, // Store all the flights in the group
    //             ];
    //         })
    //         ->sortBy(function ($group) {
    //             return \Carbon\Carbon::parse($group['week']); // Sort by the 'week' date (which is the first flight's date)
    //         });

    //     // Find the second busiest week
    //     $busiestWeek = $weeklyFlightCounts
    //         ->sortByDesc('count') // Sort by the number of flights in descending order
    //         ->skip(1) // Skip the first result (the busiest week)
    //         ->take(1) // Limit the result to only 1 (get the second busiest week)
    //         ->first(); // Get the first item, which is the second busiest week's flights

    //     // Get flights for the busiest week
    //     $finalFlights = $busiestWeek ? $busiestWeek['flights'] : collect();
    //     $flightanne = $finalFlights->pluck('saison')->all();

    //     // Check if there are any seasons available and set $this->annee
    //     $this->annee = !empty($flightanne) ? $flightanne[0] : 'N/A';
    //     $flightDates = $finalFlights->pluck('flight_date')->all();
    //     if (!empty($flightDates)) {
    //         $this->min_date_vol = Carbon::createFromFormat('Y-m-d', min($flightDates))->locale('fr_FR')->isoFormat('DD');
    //         $this->max_date_vol = Carbon::createFromFormat('Y-m-d', max($flightDates))->locale('fr_FR')->isoFormat('DD MMMM YYYY');
    //     } else {
    //         $this->min_date_vol = null;
    //         $this->max_date_vol = null;
    //     }


    //     // Process final results
    //     $retour = [];
    //     foreach ($finalFlights as $val) {
    //         $separator = $val->numero_arrivee != "" && $val->numero_depart != "" ? "/" : "";
    //         $company = $val->arrivee_company ? $val->arrivee_company : $val->depart_company;
    //         $numero = $company . ' ' . $val->numero_arrivee . $separator . $val->numero_depart;

    //         $retour[] = [
    //             'date_vol' => $val->flight_date,
    //             'numero' => $numero,
    //             'type_app' => $val->equipement,
    //             'capacite' => $val->capacite,
    //             'assistant' => $val->assist,
    //             'arrivee' => $val->arrivee,
    //             'heure_arrive' => $val->heure_arrive,
    //             'depart' => $val->depart,
    //             'heure_depart' => $val->heure_depart,
    //         ];
    //     }
    //     $finalresult = $this->removeDuplicateFlightsSomaine($retour);
    //     // echo "<pre>";
    //     // print_r($finalresult);
    //     // echo "</pre>";
    //     // die;
    //     // Sort the result by 'date_vol'
    //     usort($finalresult, function ($a, $b) {
    //         return strtotime($a['date_vol']) - strtotime($b['date_vol']);
    //     });

    //     // Output the result


    //     // Calculate and log execution time
    //     $executionTime = microtime(true) - $startTime;
    //     Log::info('Execution Time:', ['time' => $executionTime]);

    //     // Convert the results to a collection
    //     return collect($finalresult);
    // }

    // public function collection()
    // {
    //     $highestLoadWeek = DB::table('vol_arrives')
    //         ->select(DB::raw("DATE_FORMAT(date_vol, '%Y-%u') AS week_year"), DB::raw('COUNT(*) AS flight_count'))
    //         ->groupBy(DB::raw("DATE_FORMAT(date_vol, '%Y-%u')"))
    //         ->orderBy('flight_count', 'DESC')
    //         ->orderBy('week_year', 'DESC')
    //         ->first();

    //     $weekYear = $highestLoadWeek->week_year;

    //     // SQL Queries
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
    //             ass.name AS assist_info,
    //             sa.annee AS saison
    //         FROM vol_arrives va
    //         JOIN avions a ON va.avion_id = a.id
    //         JOIN companies c ON va.companie_id = c.id
    //         LEFT JOIN name_assist ass ON c.nom = ass.code
    //         LEFT JOIN saisons sa ON va.saison_id = sa.id
    //         WHERE DATE_FORMAT(date_vol, '%Y-%u') = ?
    //         ORDER BY va.date_vol, va.heure_arrive
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
    //             ass.name AS assist_info,
    //             sa.annee AS saison
    //         FROM vol_departs vd
    //         JOIN avions a ON vd.avion_id = a.id
    //         JOIN companies c ON vd.companie_id = c.id
    //         LEFT JOIN name_assist ass ON c.nom = ass.code
    //         LEFT JOIN saisons sa ON vd.saison_id = sa.id
    //         WHERE DATE_FORMAT(date_vol, '%Y-%u') = ?
    //         ORDER BY vd.date_vol, vd.heure_depart
    //     ";

    //     // Fetch flight data
    //     $arrives = DB::select($sql_arrive, [$weekYear]);
    //     $departs = DB::select($sql_depart, [$weekYear]);

    //     // Fetch city codes
    //     $cityCodes = DB::table('city_codes')->get()->keyBy('code');

    //     // Initialize result array
    //     $resultat = [];
    //     $dateVols = [];

    //     // Process arrivals
    //     foreach ($arrives as $arrive) {
    //         $adjusted_date_vol = $arrive->heure_arrive >= '2310' && $arrive->heure_arrive <= '2359'
    //             ? date('Y-m-d', strtotime($arrive->date_vol . ' +1 day'))
    //             : $arrive->date_vol;

    //         $dayOfWeek = Carbon::createFromFormat('Y-m-d', $adjusted_date_vol)->locale('fr_FR')->isoFormat('dddd');

    //         // Fetch departure flights matching this arrival
    //         $freres = DB::table('vol_freres')
    //             ->where('numero_arrivee', $arrive->numero)
    //             ->pluck('numero_depart')
    //             ->toArray();

    //         // Avoid adding duplicates
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
    //                 "saison" => $arrive->saison,
    //                 "freres" => $freres,
    //             ];
    //         }

    //         $dateVols[] = $adjusted_date_vol;
    //     }

    //     // Process departures
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
    //             // If no corresponding arrival flight found, create a new entry
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
    //                 "saison" => $depart->saison,
    //             ];
    //         }

    //         $dateVols[] = $depart->date_vol;
    //     }

    //     // Calculate min/max dates
    //     $this->min_date_vol = Carbon::createFromFormat('Y-m-d', min($dateVols))->locale('fr_FR')->isoFormat('DD');
    //     $this->max_date_vol = Carbon::createFromFormat('Y-m-d', max($dateVols))->locale('fr_FR')->isoFormat('DD MMMM YYYY');

    //     // Flatten results and calculate min/max dates
    //     $flattenedResult = [];

    //     // Set annee based on results
    //     $this->annee = !empty($resultat) ? $arrives[0]->saison : 'N/A';

    //     foreach ($resultat as $vols) {
    //         foreach ($vols as $vol) {
    //             $companyInfo = $vol['company_name_arrive'] ? $vol['company_name_arrive'] . ' ' . $vol['num_arrive'] : '';
    //             $companyInfo .= $vol['company_name_depart'] ? ($vol['company_name_arrive'] ? '/' : '') . $vol['num_depart'] : '';

    //             $flattenedResult[] = [
    //                 'date_vol' => $vol['date_vols'][0],
    //                 'numero' => $companyInfo,
    //                 'type_app' => $vol['equipement'],
    //                 'capacite' => $vol['capacite'],
    //                 'assistant' => $vol['assist_info'],
    //                 'arrivee' => $vol['depart'],
    //                 'heure_arrive' => $vol['heure_arrive'],
    //                 'depart' => $vol['destination'],
    //                 'heure_depart' => $vol['heure_depart'],
    //             ];
    //         }
    //     }

    //     return collect($flattenedResult);
    // }

    // private function removeDuplicateFlightsSomaine($flights) {
    //     // Group the flights based on the unique key (all fields we want to avoid duplication on)
    //     $uniqueFlights = [];

    //     foreach ($flights as $flight) {
    //         // Check if `depart` or `heure_depart` is empty, skip this flight
    //         if (empty($flight['depart']) || empty($flight['heure_depart'])) {
    //             continue; // Skip this flight and move to the next
    //         }

    //         // Build the unique key
    //         $key = $flight['depart'] . $flight['heure_depart'] . $flight['type_app'] . $flight['capacite'] .
    //                $flight['assistant'] . $flight['date_vol'];

    //         // Check if this flight with the same key exists in the array
    //         if (isset($uniqueFlights[$key])) {
    //             // If the flight exists, update its arrival details
    //             if (!empty($flight['numero_arrivee'])) {
    //                 $uniqueFlights[$key]['arrivee'] = $flight['arrivee'];
    //                 $uniqueFlights[$key]['heure_arrive'] = $flight['heure_arrive'];
    //             }
    //         } else {
    //             // Otherwise, add the flight as a new entry
    //             $uniqueFlights[$key] = $flight;
    //         }
    //     }

    //     // Return the unique flights array
    //     return array_values($uniqueFlights); // Reset array keys
    // }

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


    public function removeDuplicateArrays($array) {
        // Serialize each array element
        $serializedArray = array_map('serialize', $array);

        // Remove duplicate serialized arrays
        $uniqueSerializedArray = array_unique($serializedArray);

        // Unserialize the arrays back to their original form
        $uniqueArray = array_map('unserialize', $uniqueSerializedArray);

        return $uniqueArray;
    }

    public function headings(): array
    {
        return [
            'Jours',
            'N° Vol',
            'Type APP',
            'Capacité',
            'Assist',
            'Provenance',
            'Heure',
            'Destination',
            'Heure',
            'Observation'
        ];
    }
    public function styles(Worksheet $sheet)
    {
        // Apply borders to all cells
        $sheet->getStyle('B13:J' . $sheet->getHighestRow())
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A12:J' . $sheet->getHighestRow())->getFont()->setSize(14);
        $sheet->getStyle('C14:J' . $sheet->getHighestRow())->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C14:J' . $sheet->getHighestRow())->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('B14:B' . $sheet->getHighestRow())->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A14:A' . $sheet->getHighestRow())->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A14:A' . $sheet->getHighestRow())->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        // Apply specific styles to the headers
        $sheet->getStyle('A14:A'.$sheet->getHighestRow())
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A14:A' . $sheet->getHighestRow())->getFont()->setSize(24)->setBold(true);
        $sheet->getStyle('B13:J13')->applyFromArray([
            'font' => [
                'bold' => true,
                'size'=>14,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],

        ]);
        $sheet->setCellValue('A13', '');
        $sheet->getRowDimension(12)->setRowHeight(30);
        $sheet->getRowDimension(13)->setRowHeight(35);
        $sheet->getRowDimension(14)->setRowHeight(20);
        $sheet->getRowDimension(1)->setRowHeight(21);
        $sheet->getRowDimension(2)->setRowHeight(21);
        $sheet->getRowDimension(3)->setRowHeight(21);
        $sheet->getRowDimension(4)->setRowHeight(7);
        $sheet->getRowDimension(5)->setRowHeight(7);
        $sheet->getRowDimension(6)->setRowHeight(8);
        $sheet->getRowDimension(7)->setRowHeight(9);




        return [];
    }

    public function startCell(): string
    {
        return 'A13';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                //Filter auto
                $event->sheet->getDelegate()->setAutoFilter('B13:I13');
                // Set values and merge cells for the custom header
                $sheet->mergeCells('A1:E1');
                $sheet->setCellValue('A1', 'Office National Des Aéroports');
                $sheet->getStyle('A1:E1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],]);
                $sheet->mergeCells('A2:E2');
                $sheet->setCellValue('A2', 'Aéroport Agadir Al Massira');
                $sheet->getStyle('A2:E2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],]);
                $sheet->mergeCells('A3:F3');
                $sheet->setCellValue('A3', 'Division Exploitation/Service Opérations Terminal');
                $sheet->getStyle('A3:F3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],]);
                // Add image to cell I1
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setPath(storage_path('app/public/logo/Logo.PNG')); // Ensure this path is correct
                $drawing->setHeight(115);
                $drawing->setCoordinates('F1');
                $drawing->setOffsetX(75); // Adjust this value as needed
                $drawing->setOffsetY(5);
                $drawing->setWorksheet($sheet);

                $sheet->mergeCells('I2:J2');
                $sheet->setCellValue('I2', 'AGA.PR02.E.052/03');
                $sheet->getStyle('I2:J2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->mergeCells('A4:J7');
                $sheet->mergeCells('A8:J8');
                $sheet->setCellValue('A8', 'Programme prévisionnel ' . $this->annee);
                $sheet->mergeCells('A9:J9');
                $sheet->setCellValue('A9', 'Semaine type du ' . $this->min_date_vol . ' au ' . $this->max_date_vol);
                // $sheet->setCellValue('A6', 'Programme prévisionnel été 2024');

                $sheet->getStyle('A4:J9')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 30,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getColumnDimension('A')->setWidth('5');
                $sheet->getColumnDimension('A')->setAutoSize(false);
                $sheet->getColumnDimension('B')->setWidth('20');
                $sheet->getColumnDimension('B')->setAutoSize(false);
                $sheet->getColumnDimension('C')->setWidth('15');
                $sheet->getColumnDimension('C')->setAutoSize(false);
                $sheet->getColumnDimension('D')->setWidth('15');
                $sheet->getColumnDimension('D')->setAutoSize(false);
                $sheet->getColumnDimension('E')->setWidth('10');
                $sheet->getColumnDimension('E')->setAutoSize(false);
                $sheet->getColumnDimension('F')->setWidth('27');
                $sheet->getColumnDimension('F')->setAutoSize(false);
                $sheet->getColumnDimension('G')->setWidth('10');
                $sheet->getColumnDimension('G')->setAutoSize(false);
                $sheet->getColumnDimension('H')->setWidth('27');
                $sheet->getColumnDimension('H')->setAutoSize(false);
                $sheet->getColumnDimension('I')->setWidth('10');
                $sheet->getColumnDimension('I')->setAutoSize(false);
                $sheet->getColumnDimension('J')->setWidth('20');
                $sheet->getColumnDimension('J')->setAutoSize(false);



                $sheet->mergeCells('A10:J10');
                $sheet->setCellValue('A10', 'Les horaires sont donnés en heure locale (GMT+1)');
                $sheet->getStyle('A10:J10')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FF0000'],
                        'size' => 16,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFFF00'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);

                // Merge cells for ARRIVEE and DEPART
                $sheet->mergeCells('F12:G12');
                $sheet->mergeCells('H12:I12');


                // Set values for merged cells
                $sheet->setCellValue('F12', 'ARRIVEE');
                $sheet->setCellValue('H12', 'DEPART');


                // Apply styles to the merged cells
                $sheet->getStyle('F12:I12')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);

                // Apply borders to the exterior of the range A1:K7
                $sheet->getStyle('A1:J3')->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);
                $sheet->getStyle('A1:J9')->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);

                $flights = $this->collection();

                // Start row for data (adjust if needed)
                $startRow = 14;
                $currentDay = null;
                $dayStartRow = $startRow;
                $customIndex = 0;
                // Specify the column to merge, for example, column 'J'
                $mergeColumn = 'A';
                $currentRow = $startRow;
                // Loop through data rows
                foreach ($flights as $flight) {
                    $currentRow = $startRow + $customIndex; // Calculate current row

                    // Check if the day has changed
                    if ($currentDay !== null && $currentDay !== $flight['date_vol']) {
                        // Merge cells for the previous day if there are multiple rows
                        if ($currentRow  > $dayStartRow) {
                            // echo "Merging cells from row $dayStartRow to " . ($currentRow-1 ) . " for day $currentDay<br>";
                            $sheet->mergeCells("{$mergeColumn}{$dayStartRow}:{$mergeColumn}" . ($currentRow - 1));
                        }

                        $dayStartRow = $currentRow; // Update the start row for the new day
                    }

                    $currentDay = $flight['date_vol']; // Update the current day
                    $customIndex++; // Increment custom index
                }


                // Merge cells for the last day
                $highestRow = $sheet->getHighestRow();
                if ($highestRow >= $dayStartRow ) {
                    // echo "Merging cells from row $dayStartRow to " . ($currentRow) . " for day $currentDay<br>";
                    $sheet->mergeCells("{$mergeColumn}{$dayStartRow}:{$mergeColumn}" . ($currentRow));

                }
                // Rotate text in the merged cells by 90 degrees
                $sheet->getStyle("{$mergeColumn}{$startRow}:{$mergeColumn}" . $highestRow)->getAlignment()->setTextRotation(90);

                // Optionally, adjust the width of the specified column
                $sheet->getColumnDimension($mergeColumn)->setWidth(5);


                $sheet->setShowGridlines(false);
            },
        ];
    }
}
