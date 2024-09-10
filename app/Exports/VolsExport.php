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

class VolsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents, WithCustomStartCell
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $annee;
    protected $selectedSeason;

    public function __construct($selectedSeason)
    {
        $this->selectedSeason = $selectedSeason;
    }
    public function collection()
    {
        $selectedSeason = $this->selectedSeason;

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
                vd.saison_id,
                sa.annee AS saison
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
            ->leftJoin('saisons as sa', function ($join) {
                $join->on('va.saison_id', '=', 'sa.id')
                    ->orOn('vd.saison_id', '=', 'sa.id');
            })->where('vd.saison_id', $selectedSeason);

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
                "" AS heure_depart,
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
                va.saison_id,
                sa.annee AS saison
            ')
            ->leftJoin('city_codes as cc_arrivee', 'va.depart', '=', 'cc_arrivee.code')
            ->leftJoin('companies as vc_arrive', 'va.companie_id', '=', 'vc_arrive.id')
            ->leftJoin('avions as a', 'va.avion_id', '=', 'a.id')
            ->leftJoin('name_assist as na', 'vc_arrive.nom', '=', 'na.code')
            ->leftJoin('saisons as sa', 'va.saison_id', '=', 'sa.id')->where('va.saison_id', $selectedSeason);

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
                    'saison' => $group->first()->saison,
                ];
        })
        ->sortBy(['jour_semaine', 'heure_arrive', 'heure_depart'])
        ->values()
        ->toArray();
        // echo "<pre>";
        // print_r($groupedFlights);
        // echo "</pre>";
        // die;
        $FinalResultat = $this->removeDuplicateFlights($groupedFlights) ;
        $retour = [] ;
        if (!empty($FinalResultat)) {
            $this->annee = $FinalResultat[0]['saison'];
        } else {
            $this->annee = 'N/A'; // Default value if no results
        }
        foreach( $FinalResultat as $key => $val ) {
            $separator = $val['numero_arrivee'] && $val['numero_depart'] ? "/" : "";
            $company = $val['arrivee_company'] ? $val['arrivee_company'] : $val['depart_company'];
            $numero = $company . ' ' . $val['numero_arrivee'] . $separator . $val['numero_depart'];



            $retour[] = [
                'jour_semaine' => $val['jour_semaine'],
                'numero' => $numero,
                'type_app' => $val['equipement'],
                'capacite' => $val['capacite'],
                'assistant' => $val['assist'],
                'arrivee' => $val['arrivee'],
                'heure_arrive' => $val['heure_arrive'],
                'depart' => $val['depart'],
                'heure_depart' => $val['heure_depart'],
                'min' => $val['date_vol_min'],
                'max' => $val['date_vol_max'],

            ] ;

        }


        // Convert the results to a collection and return
        return collect($retour);
    }


    // public function collection()
    // {
    //     DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY',''))");
    //     $vols = DB::select("WITH FlightData AS (
    //         SELECT DISTINCT
    //             COALESCE(va.numero, '') AS numero_arrivee,
    //             COALESCE(cc_arrivee.name, '--') AS arrivee,
    //             vc_arrive.nom AS arrivee_company,
    //             CASE
    //                 WHEN va.heure_arrive IS NOT NULL THEN CONCAT(SUBSTR(va.heure_arrive, 1, 2), 'h', SUBSTR(va.heure_arrive, 3, 2))
    //                 ELSE '--'
    //             END AS heure_arrive,
    //             COALESCE(vd.numero, '') AS numero_depart,
    //             COALESCE(cc_depart.name, '--') AS depart,
    //             CASE
    //                 WHEN vd.heure_depart IS NOT NULL THEN CONCAT(SUBSTR(vd.heure_depart, 1, 2), 'h', SUBSTR(vd.heure_depart, 3, 2))
    //                 ELSE '--'
    //             END AS heure_depart,
    //             vc_depart.nom AS depart_company,
    //             a.equipement,
    //             a.capacite,
    //             na.name AS assist,
    //             sa.annee AS saison,
    //             CASE DAYNAME(COALESCE(va.date_vol, vd.date_vol))
    //                 WHEN 'Monday' THEN 'lundi'
    //                 WHEN 'Tuesday' THEN 'mardi'
    //                 WHEN 'Wednesday' THEN 'mercredi'
    //                 WHEN 'Thursday' THEN 'jeudi'
    //                 WHEN 'Friday' THEN 'vendredi'
    //                 WHEN 'Saturday' THEN 'samedi'
    //                 WHEN 'Sunday' THEN 'dimanche'
    //                 ELSE DAYNAME(COALESCE(va.date_vol, vd.date_vol))
    //             END AS jour_semaine,
    //             COALESCE(va.date_vol, vd.date_vol) AS flight_date
    //         FROM
    //             vol_departs vd
    //         LEFT JOIN
    //             vol_freres vf ON vd.numero = vf.numero_depart
    //         LEFT JOIN
    //             vol_arrives va ON vf.numero_arrivee = va.numero
    //             AND va.date_vol = CASE
    //                 WHEN va.heure_arrive BETWEEN '2310' AND '2359'
    //                 THEN DATE_ADD(vd.date_vol, INTERVAL -1 DAY)
    //                 ELSE vd.date_vol
    //             END
    //         LEFT JOIN
    //             city_codes cc_depart ON vd.destination = cc_depart.code
    //         LEFT JOIN
    //             city_codes cc_arrivee ON va.depart = cc_arrivee.code
    //         LEFT JOIN
    //             companies vc_arrive ON va.companie_id = vc_arrive.id
    //         LEFT JOIN
    //             companies vc_depart ON vd.companie_id = vc_depart.id
    //         LEFT JOIN
    //             avions a ON va.avion_id = a.id OR vd.avion_id = a.id
    //         LEFT JOIN
    //             name_assist na ON vc_arrive.nom = na.code OR vc_depart.nom = na.code
    //         LEFT JOIN
    //             saisons sa ON va.saison_id = sa.id OR vd.saison_id = sa.id
    //         UNION ALL

    //         SELECT DISTINCT
    //             COALESCE(va.numero, '') AS numero_arrivee,
    //             COALESCE(cc_arrivee.name, '--') AS arrivee,
    //             vc_arrive.nom AS arrivee_company,
    //             CASE
    //                 WHEN va.heure_arrive IS NOT NULL THEN CONCAT(SUBSTR(va.heure_arrive, 1, 2), 'h', SUBSTR(va.heure_arrive, 3, 2))
    //                 ELSE '--'
    //             END AS heure_arrive,
    //             COALESCE(vd.numero, '') AS numero_depart,
    //             COALESCE(cc_depart.name, '--') AS depart,
    //             CASE
    //                 WHEN vd.heure_depart IS NOT NULL THEN CONCAT(SUBSTR(vd.heure_depart, 1, 2), 'h', SUBSTR(vd.heure_depart, 3, 2))
    //                 ELSE '--'
    //             END AS heure_depart,
    //             vc_depart.nom AS depart_company,
    //             a.equipement,
    //             a.capacite,
    //             na.name AS assist,
    //             sa.annee AS saison,
    //             CASE DAYNAME(COALESCE(va.date_vol, vd.date_vol))
    //                 WHEN 'Monday' THEN 'lundi'
    //                 WHEN 'Tuesday' THEN 'mardi'
    //                 WHEN 'Wednesday' THEN 'mercredi'
    //                 WHEN 'Thursday' THEN 'jeudi'
    //                 WHEN 'Friday' THEN 'vendredi'
    //                 WHEN 'Saturday' THEN 'samedi'
    //                 WHEN 'Sunday' THEN 'dimanche'
    //                 ELSE DAYNAME(COALESCE(va.date_vol, vd.date_vol))
    //             END AS jour_semaine,
    //             COALESCE(va.date_vol, vd.date_vol) AS flight_date
    //         FROM
    //             vol_arrives va
    //         LEFT JOIN
    //             vol_freres vf ON va.numero = vf.numero_arrivee
    //         LEFT JOIN
    //             vol_departs vd ON vf.numero_depart = vd.numero
    //             AND vd.date_vol = CASE
    //                 WHEN vd.heure_depart BETWEEN '2310' AND '2359'
    //                 THEN DATE_ADD(va.date_vol, INTERVAL -1 DAY)
    //                 ELSE va.date_vol
    //             END
    //         LEFT JOIN
    //             city_codes cc_depart ON vd.destination = cc_depart.code
    //         LEFT JOIN
    //             city_codes cc_arrivee ON va.depart = cc_arrivee.code
    //         LEFT JOIN
    //             companies vc_arrive ON va.companie_id = vc_arrive.id
    //         LEFT JOIN
    //             companies vc_depart ON vd.companie_id = vc_depart.id
    //         LEFT JOIN
    //             avions a ON va.avion_id = a.id OR vd.avion_id = a.id
    //         LEFT JOIN
    //             name_assist na ON vc_arrive.nom = na.code OR vc_depart.nom = na.code
    //         LEFT JOIN
    //             saisons sa ON va.saison_id = sa.id OR vd.saison_id = sa.id
    //     )
    //     SELECT
    //         numero_arrivee,
    //         arrivee,
    //         arrivee_company,
    //         heure_arrive,
    //         numero_depart,
    //         depart,
    //         heure_depart,
    //         depart_company,
    //         equipement,
    //         capacite,
    //         assist,
    //         saison,
    //         jour_semaine,
    //         MIN(flight_date) AS date_vol_min,
    //         MAX(flight_date) AS date_vol_max
    //     FROM
    //         FlightData
    //     GROUP BY
    //         numero_arrivee,
    //         numero_depart,
    //         arrivee,
    //         arrivee_company,
    //         heure_arrive,
    //         depart,
    //         heure_depart,
    //         depart_company,
    //         equipement,
    //         capacite,
    //         assist,
    //         jour_semaine
    //     ORDER BY
    //         FIELD(jour_semaine, 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'),
    //         heure_arrive,
    //         heure_depart;

    //     ");
    //     if (!empty($vols)) {
    //         $this->annee = $vols[0]->saison;
    //     } else {
    //         $this->annee = 'N/A'; // Default value if no results
    //     }
    //     foreach( $vols as $key => $val ) {
    //         $separator = $val->numero_arrivee != "" && $val->numero_depart != "" ? "/" : "" ;
    //         $company = $val->arrivee_company ? $val->arrivee_company : $val->depart_company ;
    //         $numero = $company .' '. $val->numero_arrivee . $separator .$val->numero_depart ;


    //         $retour[] = [
    //             'jour_semaine' => $val->jour_semaine,
    //             'numero' => $numero,
    //             'type_app' => $val->equipement ,
    //             'capacite' => $val->capacite ,
    //             'assistant' => $val->assist,
    //             'arrivee' => $val->arrivee ,
    //             'heure_arrive' => $val->heure_arrive ,
    //             'depart' => $val->depart ,
    //             'heure_depart' => $val->heure_depart,
    //             'min' => $val->date_vol_min,
    //             'max' => $val->date_vol_max

    //         ] ;

    //     }

    //     $retour = $this->removeDuplicateArrays($retour) ;


    //     return collect($retour);

    // }
    // public function collection()
    // {
    //         // Define SQL queries for arrivals and departures
    //         $sql_arrive = "
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
    //         ORDER BY va.date_vol
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
    //         ORDER BY vd.date_vol
    //     ";

    //     // Fetch flight data
    //     $arrives = DB::select($sql_arrive);
    //     $departs = DB::select($sql_depart);

    //     // Fetch city codes
    //     $cityCodes = DB::table('city_codes')->get()->keyBy('code');

    //     // Initialize result array
    //     $resultat = [];

    //     // Process arrivals
    //     foreach ($arrives as $arrive) {
    //         $adjusted_date_vol = $arrive->heure_arrive >= '2310' && $arrive->heure_arrive <= '2359'
    //             ? date('Y-m-d', strtotime($arrive->date_vol . ' 1 day'))
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
    //                 "freres" => $freres,
    //             ];
    //         }
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
    //             ];
    //         }
    //     }

    //     // Flatten results and calculate min/max dates
    //     $flattenedResult = [];
    //     foreach ($resultat as $dayOfWeek => $vols) {
    //         foreach ($vols as $vol) {
    //             $min_date_vol = min($vol['date_vols']);
    //             $max_date_vol = max($vol['date_vols']);

    //             $companyInfo = $vol['company_name_arrive'] ? $vol['company_name_arrive'] . ' ' . $vol['num_arrive'] : '';
    //             $companyInfo .= $vol['company_name_depart'] ? ($vol['company_name_arrive'] ? '/' : '') . $vol['num_depart'] : '';

    //             $flattenedResult[] = array_merge(['day_of_week' => $dayOfWeek], $vol, [
    //                 'company_info' => $companyInfo,
    //                 'min_date' => $min_date_vol,
    //                 'max_date' => $max_date_vol,
    //             ]);
    //         }
    //     }

    //         // Format the data as per your request
    //         $retour = [];
    //         foreach ($flattenedResult as $val) {
    //             $retour[] = [
    //                 'jour_semaine' => $val['day_of_week'],
    //                 'numero' => $val['company_info'],
    //                 'type_app' => $val['equipement'],
    //                 'capacite' => $val['capacite'],
    //                 'assistant' => $val['assist_info'],
    //                 'arrivee' => $val['depart'],
    //                 'heure_arrive' => $val['heure_arrive'],
    //                 'depart' => $val['destination'],
    //                 'heure_depart' => $val['heure_depart'],
    //                 'min' => $val['min_date'],
    //                 'max' => $val['max_date']
    //             ];
    //         }

    //         return collect($retour);
    // }
//new code
    // public function collection(Request $request)
    // {
    //     $selectedSaison = $request->input('saison'); // Get the selected season from the request

    //     DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY',''))");
    //     $query = "WITH FlightData AS (
    //         SELECT DISTINCT
    //             COALESCE(va.numero, '') AS numero_arrivee,
    //             COALESCE(cc_arrivee.name, '--') AS arrivee,
    //             vc_arrive.nom AS arrivee_company,
    //             CASE
    //                 WHEN va.heure_arrive IS NOT NULL THEN CONCAT(SUBSTR(va.heure_arrive, 1, 2), 'h', SUBSTR(va.heure_arrive, 3, 2))
    //                 ELSE '--'
    //             END AS heure_arrive,
    //             COALESCE(vd.numero, '') AS numero_depart,
    //             COALESCE(cc_depart.name, '--') AS depart,
    //             CASE
    //                 WHEN vd.heure_depart IS NOT NULL THEN CONCAT(SUBSTR(vd.heure_depart, 1, 2), 'h', SUBSTR(vd.heure_depart, 3, 2))
    //                 ELSE '--'
    //             END AS heure_depart,
    //             vc_depart.nom AS depart_company,
    //             a.equipement,
    //             a.capacite,
    //             na.name AS assist,
    //             sa.annee AS saison,
    //             CASE DAYNAME(COALESCE(va.date_vol, vd.date_vol))
    //                 WHEN 'Monday' THEN 'lundi'
    //                 WHEN 'Tuesday' THEN 'mardi'
    //                 WHEN 'Wednesday' THEN 'mercredi'
    //                 WHEN 'Thursday' THEN 'jeudi'
    //                 WHEN 'Friday' THEN 'vendredi'
    //                 WHEN 'Saturday' THEN 'samedi'
    //                 WHEN 'Sunday' THEN 'dimanche'
    //                 ELSE DAYNAME(COALESCE(va.date_vol, vd.date_vol))
    //             END AS jour_semaine,
    //             COALESCE(va.date_vol, vd.date_vol) AS flight_date
    //         FROM
    //             vol_departs vd
    //         LEFT JOIN
    //             vol_freres vf ON vd.numero = vf.numero_depart
    //         LEFT JOIN
    //             vol_arrives va ON vf.numero_arrivee = va.numero
    //             AND va.date_vol = CASE
    //                 WHEN va.heure_arrive BETWEEN '2310' AND '2359'
    //                 THEN DATE_ADD(vd.date_vol, INTERVAL -1 DAY)
    //                 ELSE vd.date_vol
    //             END
    //         LEFT JOIN
    //             city_codes cc_depart ON vd.destination = cc_depart.code
    //         LEFT JOIN
    //             city_codes cc_arrivee ON va.depart = cc_arrivee.code
    //         LEFT JOIN
    //             companies vc_arrive ON va.companie_id = vc_arrive.id
    //         LEFT JOIN
    //             companies vc_depart ON vd.companie_id = vc_depart.id
    //         LEFT JOIN
    //             avions a ON va.avion_id = a.id OR vd.avion_id = a.id
    //         LEFT JOIN
    //             name_assist na ON vc_arrive.nom = na.code OR vc_depart.nom = na.code
    //         LEFT JOIN
    //             saisons sa ON va.saison_id = sa.id OR vd.saison_id = sa.id
    //         UNION ALL
    //         SELECT DISTINCT
    //             COALESCE(va.numero, '') AS numero_arrivee,
    //             COALESCE(cc_arrivee.name, '--') AS arrivee,
    //             vc_arrive.nom AS arrivee_company,
    //             CASE
    //                 WHEN va.heure_arrive IS NOT NULL THEN CONCAT(SUBSTR(va.heure_arrive, 1, 2), 'h', SUBSTR(va.heure_arrive, 3, 2))
    //                 ELSE '--'
    //             END AS heure_arrive,
    //             COALESCE(vd.numero, '') AS numero_depart,
    //             COALESCE(cc_depart.name, '--') AS depart,
    //             CASE
    //                 WHEN vd.heure_depart IS NOT NULL THEN CONCAT(SUBSTR(vd.heure_depart, 1, 2), 'h', SUBSTR(vd.heure_depart, 3, 2))
    //                 ELSE '--'
    //             END AS heure_depart,
    //             vc_depart.nom AS depart_company,
    //             a.equipement,
    //             a.capacite,
    //             na.name AS assist,
    //             sa.annee AS saison,
    //             CASE DAYNAME(COALESCE(va.date_vol, vd.date_vol))
    //                 WHEN 'Monday' THEN 'lundi'
    //                 WHEN 'Tuesday' THEN 'mardi'
    //                 WHEN 'Wednesday' THEN 'mercredi'
    //                 WHEN 'Thursday' THEN 'jeudi'
    //                 WHEN 'Friday' THEN 'vendredi'
    //                 WHEN 'Saturday' THEN 'samedi'
    //                 WHEN 'Sunday' THEN 'dimanche'
    //                 ELSE DAYNAME(COALESCE(va.date_vol, vd.date_vol))
    //             END AS jour_semaine,
    //             COALESCE(va.date_vol, vd.date_vol) AS flight_date
    //         FROM
    //             vol_arrives va
    //         LEFT JOIN
    //             vol_freres vf ON va.numero = vf.numero_arrivee
    //         LEFT JOIN
    //             vol_departs vd ON vf.numero_depart = vd.numero
    //             AND vd.date_vol = CASE
    //                 WHEN vd.heure_depart BETWEEN '2310' AND '2359'
    //                 THEN DATE_ADD(va.date_vol, INTERVAL -1 DAY)
    //                 ELSE va.date_vol
    //             END
    //         LEFT JOIN
    //             city_codes cc_depart ON vd.destination = cc_depart.code
    //         LEFT JOIN
    //             city_codes cc_arrivee ON va.depart = cc_arrivee.code
    //         LEFT JOIN
    //             companies vc_arrive ON va.companie_id = vc_arrive.id
    //         LEFT JOIN
    //             companies vc_depart ON vd.companie_id = vc_depart.id
    //         LEFT JOIN
    //             avions a ON va.avion_id = a.id OR vd.avion_id = a.id
    //         LEFT JOIN
    //             name_assist na ON vc_arrive.nom = na.code OR vc_depart.nom = na.code
    //         LEFT JOIN
    //             saisons sa ON va.saison_id = sa.id OR vd.saison_id = sa.id
    //     )
    //     SELECT
    //         numero_arrivee,
    //         arrivee,
    //         arrivee_company,
    //         heure_arrive,
    //         numero_depart,
    //         depart,
    //         heure_depart,
    //         depart_company,
    //         equipement,
    //         capacite,
    //         assist,
    //         saison,
    //         jour_semaine,
    //         MIN(flight_date) AS date_vol_min,
    //         MAX(flight_date) AS date_vol_max
    //     FROM
    //         FlightData
    //     WHERE
    //         :selectedSaison IS NULL OR saison = :selectedSaison
    //     GROUP BY
    //         numero_arrivee,
    //         numero_depart,
    //         arrivee,
    //         arrivee_company,
    //         heure_arrive,
    //         depart,
    //         heure_depart,
    //         depart_company,
    //         equipement,
    //         capacite,
    //         assist,
    //         jour_semaine
    //     ORDER BY
    //         FIELD(jour_semaine, 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'),
    //         heure_arrive,
    //         heure_depart;";

    //     $vols = DB::select($query, ['selectedSaison' => $selectedSaison]);

    //     if (!empty($vols)) {
    //         $this->annee = $vols[0]->saison;
    //     } else {
    //         $this->annee = 'N/A'; // Default value if no results
    //     }

    //     $retour = [];

    //     foreach ($vols as $key => $val) {
    //         $separator = $val->numero_arrivee != "" && $val->numero_depart != "" ? "/" : "";
    //         $company = $val->arrivee_company ? $val->arrivee_company : $val->depart_company;
    //         $numero = $company . ' ' . $val->numero_arrivee . $separator . $val->numero_depart;

    //         $retour[] = [
    //             'jour_semaine' => $val->jour_semaine,
    //             'numero' => $numero,
    //             'type_app' => $val->equipement,
    //             'capacite' => $val->capacite,
    //             'assistant' => $val->assist,
    //             'arrivee' => $val->arrivee,
    //             'heure_arrive' => $val->heure_arrive,
    //             'depart' => $val->depart,
    //             'heure_depart' => $val->heure_depart,
    //             'min' => $val->date_vol_min,
    //             'max' => $val->date_vol_max,
    //         ];
    //     }

    //     $retour = $this->removeDuplicateArrays($retour);

    //     return collect($retour);
    // }


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
            'Début',
            'Fin'
        ];
    }
    public function styles(Worksheet $sheet)
    {
        // Apply borders to all cells
        $sheet->getStyle('B10:K' . $sheet->getHighestRow())
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A9:K' . $sheet->getHighestRow())->getFont()->setSize(14);
        $sheet->getStyle('C11:K' . $sheet->getHighestRow())->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C11:K' . $sheet->getHighestRow())->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('B11:B' . $sheet->getHighestRow())->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A11:A' . $sheet->getHighestRow())->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A11:A' . $sheet->getHighestRow())->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        // Apply specific styles to the headers
        $sheet->getStyle('A11:A'.$sheet->getHighestRow())
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A11:A' . $sheet->getHighestRow())->getFont()->setSize(24)->setBold(true);
        $sheet->getStyle('B10:K10')->applyFromArray([
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
        $sheet->setCellValue('A10', '');
        $sheet->getRowDimension(9)->setRowHeight(30);
        $sheet->getRowDimension(10)->setRowHeight(35);
        $sheet->getRowDimension(11)->setRowHeight(20);
        $sheet->getRowDimension(1)->setRowHeight(21);
        $sheet->getRowDimension(2)->setRowHeight(21);
        $sheet->getRowDimension(3)->setRowHeight(21);
        $sheet->getRowDimension(5)->setRowHeight(21);




        return [];
    }

    public function startCell(): string
    {
        return 'A10';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                //Filter auto
                $event->sheet->getDelegate()->setAutoFilter('B10:K10');
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

                $sheet->mergeCells('J2:K2');
                $sheet->setCellValue('J2', 'AGA.PR02.E.052/03');
                $sheet->getStyle('J2:K2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);
                // Merge cells for the main header
                $sheet->mergeCells('A4:k5');
                $sheet->mergeCells('A6:k6');
                $sheet->setCellValue('A6', 'Programme prévisionnel ' . $this->annee);
                $sheet->getStyle('A4:K6')->applyFromArray([
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
                $sheet->getColumnDimension('F')->setWidth('30');
                $sheet->getColumnDimension('F')->setAutoSize(false);
                $sheet->getColumnDimension('G')->setWidth('10');
                $sheet->getColumnDimension('G')->setAutoSize(false);
                $sheet->getColumnDimension('H')->setWidth('30');
                $sheet->getColumnDimension('H')->setAutoSize(false);
                $sheet->getColumnDimension('I')->setWidth('10');
                $sheet->getColumnDimension('I')->setAutoSize(false);
                $sheet->getColumnDimension('J')->setWidth('20');
                $sheet->getColumnDimension('J')->setAutoSize(false);
                $sheet->getColumnDimension('K')->setWidth('20');
                $sheet->getColumnDimension('K')->setAutoSize(false);


                $sheet->mergeCells('A7:K7');
                $sheet->setCellValue('A7', 'Les horaires sont donnés en heure locale (GMT+1)');
                $sheet->getStyle('A7:K7')->applyFromArray([
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
                $sheet->mergeCells('F9:G9');
                $sheet->mergeCells('H9:I9');
                $sheet->mergeCells('J9:K9');

                // Set values for merged cells
                $sheet->setCellValue('F9', 'ARRIVEE');
                $sheet->setCellValue('H9', 'DEPART');
                $sheet->setCellValue('J9', 'Période');

                // Apply styles to the merged cells
                $sheet->getStyle('F9:K9')->applyFromArray([
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
                $sheet->getStyle('A1:K3')->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);
                $sheet->getStyle('A1:K7')->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);

                $flights = $this->collection();

                // Start row for data (adjust if needed)
                $startRow = 11;
                $currentDay = null;
                $dayStartRow = $startRow;
                $customIndex = 0;
                // Specify the column to merge, for example, column 'J'
                $mergeColumn = 'A';

                // Loop through data rows
                foreach ($flights as $flight) {
                    $currentRow = $startRow + $customIndex; // Calculate current row

                    // Check if the day has changed
                    if ($currentDay !== null && $currentDay !== $flight['jour_semaine']) {
                        // Merge cells for the previous day if there are multiple rows
                        if ($currentRow  > $dayStartRow) {
                            // echo "Merging cells from row $dayStartRow to " . ($currentRow-1 ) . " for day $currentDay<br>";
                            $sheet->mergeCells("{$mergeColumn}{$dayStartRow}:{$mergeColumn}" . ($currentRow - 1));
                        }

                        $dayStartRow = $currentRow; // Update the start row for the new day
                    }

                    $currentDay = $flight['jour_semaine']; // Update the current day
                    $customIndex++; // Increment custom index
                }


                // Merge cells for the last day
                $highestRow = $sheet->getHighestRow();
                if ($highestRow >= $dayStartRow ) {
                    // echo "Merging cells from row $dayStartRow to " . ($currentRow) . " for day $currentDay<br>";
                    $sheet->mergeCells("{$mergeColumn}{$dayStartRow}:{$mergeColumn}" . ($currentRow));

                }
                // Rotate text in the merged cells by 90 degrees
                $sheet->getStyle("{$mergeColumn}11:{$mergeColumn}" . $highestRow)->getAlignment()->setTextRotation(90);

                // Optionally, adjust the width of the specified column
                $sheet->getColumnDimension($mergeColumn)->setWidth(5);


                $sheet->setShowGridlines(false);
            },
        ];
    }
}
