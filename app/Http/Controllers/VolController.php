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

class VolController extends Controller
{

    public function saisonnier()
    {
        return view("pages.programme.saisonnier");
    }
    public function somaine()
    {
        return view("pages.programme.somaine");
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

    // public function datatable_saisonnier(Request $request)
    // {
    //     $mouvement = $request->input('mouvement', '');
    //     $volArrives = DB::table('vol_arrives')->get()->keyBy('numero');
    //     $volDeparts = DB::table('vol_departs')->get()->keyBy('numero');
    //     $volFreres = DB::table('vol_freres')->get();
    //     $cityCodes = DB::table('city_codes')->get()->keyBy('code');
    //     $companies = DB::table('companies')->get()->keyBy('id');
    //     $avions = DB::table('avions')->get()->keyBy('id');

    //     function formatTime($time) {
    //         return $time ? substr($time, 0, 2) . 'h' . substr($time, 2, 2) : '-';
    //     }

    //     $vols = [];

    //     foreach ($volArrives as $numero => $va) {
    //         $vf = $volFreres->firstWhere('numero_arrivee', $numero);
    //         $vd = $vf ? $volDeparts->get($vf->numero_depart) : null;

    //         $vols[] = (object) [
    //             'numero_arrivee' => $numero,
    //             'arrivee' => $cityCodes->get($va->depart)->name ?? '-',
    //             'arrivee_company' => $companies->get($va->companie_id)->nom ?? '',
    //             'heure_arrive' => formatTime($va->heure_arrive),
    //             'numero_depart' => $vd->numero ?? '',
    //             'depart' => $vd ? ($cityCodes->get($vd->destination)->name ?? '-') : '-',
    //             'depart_company' => $vd ? ($companies->get($vd->companie_id)->nom ?? '') : '',
    //             'heure_depart' => $vd ? formatTime($vd->heure_depart) : '-',
    //             'date_vol' => $va->date_vol,
    //             'equipement' => $avions->get($va->avion_id)->equipement ?? ($vd ? ($avions->get($vd->avion_id)->equipement ?? '') : ''),
    //             'capacite' => $avions->get($va->avion_id)->capacite ?? ($vd ? ($avions->get($vd->avion_id)->capacite ?? '') : '')
    //         ];
    //     }

    //     foreach ($volDeparts as $numero => $vd) {
    //         if (!collect($vols)->contains('numero_depart', $numero)) {
    //             $vf = $volFreres->firstWhere('numero_depart', $numero);
    //             $va = $vf ? $volArrives->get($vf->numero_arrivee) : null;

    //             $vols[] = (object) [
    //                 'numero_arrivee' => $va->numero ?? '',
    //                 'arrivee' => $va ? ($cityCodes->get($va->depart)->name ?? '-') : '-',
    //                 'arrivee_company' => $va ? ($companies->get($va->companie_id)->nom ?? '') : '',
    //                 'heure_arrive' => $va ? formatTime($va->heure_arrive) : '-',
    //                 'numero_depart' => $numero,
    //                 'depart' => $cityCodes->get($vd->destination)->name ?? '-',
    //                 'depart_company' => $companies->get($vd->companie_id)->nom ?? '',
    //                 'heure_depart' => formatTime($vd->heure_depart),
    //                 'date_vol' => $vd->date_vol,
    //                 'equipement' => $avions->get($vd->avion_id)->equipement ?? ($va ? ($avions->get($va->avion_id)->equipement ?? '') : ''),
    //                 'capacite' => $avions->get($vd->avion_id)->capacite ?? ($va ? ($avions->get($va->avion_id)->capacite ?? '') : '')
    //             ];
    //         }
    //     }

    //     usort($vols, fn($a, $b) => strcmp($a->date_vol, $b->date_vol));

    //     return DataTables::of($vols)->addColumn('numero', function ($vol) {
    //         $separator = $vol->numero_depart && $vol->numero_arrivee ? "/" : "";
    //         $companyInfo = "";

    //         if ($vol->depart_company && $vol->arrivee_company) {
    //             $companyInfo = $vol->arrivee_company . " " . $vol->numero_arrivee . $separator . " " . $vol->numero_depart;
    //         } elseif ($vol->arrivee_company) {
    //             $companyInfo = $vol->arrivee_company . " " . $vol->numero_arrivee;
    //         } elseif ($vol->depart_company) {
    //             $companyInfo = $vol->depart_company . " " . $vol->numero_depart;
    //         }

    //         return $companyInfo;
    //     })
    //     ->rawColumns(['numero'])
    //     ->toJson();
    // }

    // public function datatable_saisonnier1(Request $request)
    // {
    //     //    // Fetch all necessary data
    //     //     $volDeparts = DB::table('vol_departs')->get()->keyBy('numero');
    //     //     $volArrives = DB::table('vol_arrives')->get()->keyBy('numero');
    //     //     $volFreres = DB::table('vol_freres')->get();
    //     //     $cityCodes = DB::table('city_codes')->get()->keyBy('code');
    //     //     $companies = DB::table('companies')->get()->keyBy('id');
    //     //     $avions = DB::table('avions')->get()->keyBy('id');

    //     //     // Helper function to format time
    //     //     function formatTime($time) {
    //     //         return $time ? substr($time, 0, 2) . 'h' . substr($time, 2, 2) : '-';
    //     //     }

    //     //     // Transform and collect the data
    //     //     $transformedVols = collect();

    //     //     foreach ($volFreres as $vf) {
    //     //         $vd = $volDeparts->get($vf->numero_depart);
    //     //         $va = $volArrives->get($vf->numero_arrivee);

    //     //         if ($vd) {
    //     //             $transformedVols->push([
    //     //                 'numero_arrivee' => $va ? $va->numero : '',
    //     //                 'arrivee' => $va && isset($cityCodes[$va->depart]) ? $cityCodes[$va->depart]->name : '-',
    //     //                 'arrivee_company' => $va && isset($companies[$va->companie_id]) ? $companies[$va->companie_id]->nom : '',
    //     //                 'heure_arrive' => $va && $va->heure_arrive ? formatTime($va->heure_arrive) : '-',
    //     //                 'numero_depart' => $vd ? $vd->numero : '',
    //     //                 'depart' => $vd && isset($cityCodes[$vd->destination]) ? $cityCodes[$vd->destination]->name : '-',
    //     //                 'depart_company' => $vd && isset($companies[$vd->companie_id]) ? $companies[$vd->companie_id]->nom : '',
    //     //                 'heure_depart' => $vd && $vd->heure_depart ? formatTime($vd->heure_depart) : '-',
    //     //                 'date_vol' => $vd ? $vd->date_vol : ($va ? $va->date_vol : null),
    //     //                 'equipement' => $vd && isset($avions[$vd->avion_id]) ? $avions[$vd->avion_id]->equipement : '',
    //     //                 'capacite' => $vd && isset($avions[$vd->avion_id]) ? $avions[$vd->avion_id]->capacite : '',
    //     //             ]);
    //     //         }

    //     //         if ($va) {
    //     //             $transformedVols->push([
    //     //                 'numero_arrivee' => $va->numero,
    //     //                 'arrivee' => isset($cityCodes[$va->depart]) ? $cityCodes[$va->depart]->name : '-',
    //     //                 'arrivee_company' => isset($companies[$va->companie_id]) ? $companies[$va->companie_id]->nom : '',
    //     //                 'heure_arrive' => $va->heure_arrive ? formatTime($va->heure_arrive) : '-',
    //     //                 'numero_depart' => $vd ? $vd->numero : '',
    //     //                 'depart' => $vd && isset($cityCodes[$vd->destination]) ? $cityCodes[$vd->destination]->name : '-',
    //     //                 'depart_company' => $vd && isset($companies[$vd->companie_id]) ? $companies[$vd->companie_id]->nom : '',
    //     //                 'heure_depart' => $vd && $vd->heure_depart ? formatTime($vd->heure_depart) : '-',
    //     //                 'date_vol' => $vd ? $vd->date_vol : ($va ? $va->date_vol : null),
    //     //                 'equipement' => $va && isset($avions[$va->avion_id]) ? $avions[$va->avion_id]->equipement : '',
    //     //                 'capacite' => $va && isset($avions[$va->avion_id]) ? $avions[$va->avion_id]->capacite : '',
    //     //             ]);
    //     //         }
    //     //     }

    //     //     // Sort the transformed data by date_vol
    //     //     $transformedVols = $transformedVols->sortBy('date_vol')->values()->all();

    //     //     // Return the data formatted for DataTables
    //     //     return DataTables::of($transformedVols)->addColumn('numero', function ($vol) {
    //     //         $separator = $vol['numero_depart'] != "" && $vol['numero_arrivee'] != "" ? "/" : "";
    //     //         $companyInfo = "";

    //     //         if ($vol['depart_company'] && $vol['arrivee_company']) {
    //     //             $companyInfo = $vol['arrivee_company'] . " " . $vol['numero_arrivee'] . $separator . " " . $vol['numero_depart'];
    //     //         } elseif ($vol['arrivee_company']) {
    //     //             $companyInfo = $vol['arrivee_company'] . " " . $vol['numero_arrivee'];
    //     //         } elseif ($vol['depart_company']) {
    //     //             $companyInfo = $vol['depart_company'] . " " . $vol['numero_depart'];
    //     //         }

    //     //         return $companyInfo;
    //     //     })
    //     //     ->rawColumns(['numero'])
    //     //     ->toJson();

    //         $sql = "SELECT DISTINCT
    //             COALESCE(va.numero, '') AS numero_arrivee,
    //             COALESCE(cc_arrivee.name, '-') AS arrivee,
    //             vc_arrive.nom AS arrivee_company,
    //             CASE
    //                 WHEN va.heure_arrive IS NOT NULL THEN CONCAT(SUBSTR(va.heure_arrive, 1, 2), 'h', SUBSTR(va.heure_arrive, 3, 2))
    //                 ELSE '-'
    //             END AS heure_arrive,
    //             COALESCE(vd.numero, '') AS numero_depart,
    //             COALESCE(cc_depart.name, '-') AS depart,
    //             vc_depart.nom AS depart_company,
    //             CASE
    //                 WHEN vd.heure_depart IS NOT NULL THEN CONCAT(SUBSTR(vd.heure_depart, 1, 2), 'h', SUBSTR(vd.heure_depart, 3, 2))
    //                 ELSE '-'
    //             END AS heure_depart,
    //             CASE
    //                 WHEN va.heure_arrive BETWEEN '2310' AND '2359'
    //                 THEN DATE_ADD(vd.date_vol, INTERVAL -1 DAY)
    //                 ELSE vd.date_vol
    //             END AS date_vol,
    //             a.equipement,
    //             a.capacite
    //         FROM
    //             vol_arrives va
    //         LEFT JOIN
    //             vol_freres vf ON va.numero = vf.numero_arrivee
    //         LEFT JOIN
    //             vol_departs vd ON vf.numero_depart = vd.numero
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

    //         UNION ALL

    //         SELECT DISTINCT
    //             va.numero AS numero_arrivee,
    //             COALESCE(cc_arrivee.name, '-') AS arrivee,
    //             vc_arrive.nom AS arrivee_company,
    //             CASE
    //                 WHEN va.heure_arrive IS NOT NULL THEN CONCAT(SUBSTR(va.heure_arrive, 1, 2), 'h', SUBSTR(va.heure_arrive, 3, 2))
    //                 ELSE '-'
    //             END AS heure_arrive,
    //             COALESCE(vd.numero, '') AS numero_depart,
    //             COALESCE(cc_depart.name, '-') AS depart,
    //             vc_depart.nom AS depart_company,
    //             CASE
    //                 WHEN vd.heure_depart IS NOT NULL THEN CONCAT(SUBSTR(vd.heure_depart, 1, 2), 'h', SUBSTR(vd.heure_depart, 3, 2))
    //                 ELSE '-'
    //             END AS heure_depart,
    //             CASE
    //                 WHEN va.heure_arrive BETWEEN '2310' AND '2359'
    //                 THEN DATE_ADD(va.date_vol, INTERVAL 1 DAY)
    //                 ELSE va.date_vol
    //             END AS date_vol,
    //             a.equipement,
    //             a.capacite
    //         FROM
    //             vol_arrives va
    //         LEFT JOIN
    //             vol_freres vf ON va.numero = vf.numero_arrivee
    //         LEFT JOIN
    //             vol_departs vd ON vf.numero_depart = vd.numero
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

    //         ORDER BY
    //             date_vol;
    //     ";

    //     $vols = DB::select($sql);

    //     return DataTables::make($vols)->addColumn("numero", function ($vol) {
    //         $separator = $vol->numero_depart != "" && $vol->numero_arrivee != "" ? "/" : "";
    //         $companyInfo = "";

    //         if ($vol->depart_company && $vol->arrivee_company) {
    //             $companyInfo = $vol->arrivee_company . " " . $vol->numero_arrivee . $separator . " " . $vol->numero_depart;
    //         } elseif ($vol->arrivee_company) {
    //             $companyInfo = $vol->arrivee_company . " " . $vol->numero_arrivee;
    //         } elseif ($vol->depart_company) {
    //             $companyInfo = $vol->depart_company . " " . $vol->numero_depart;
    //         }

    //         return $companyInfo;
    //     })
    //     ->rawColumns(['numero'])
    //     ->toJson();



    // }

    public function datatable_somaine()
    {

        $sql = "WITH ALL_VOLS AS (
            SELECT
                DATE_FORMAT(va.date_vol, '%Y-%u') AS week_year,
                COUNT(*) AS flight_count
            FROM
                vol_arrives va
            GROUP BY
                DATE_FORMAT(va.date_vol, '%Y-%u')
            ORDER BY
                flight_count DESC ,
                week_year DESC
            LIMIT 1
        )
        SELECT
            COALESCE(va.numero, '') AS numero_arrivee,
            COALESCE(cc_arrivee.name, '-') AS arrivee,
            vc_arrive.nom AS arrivee_company,
            CASE
                WHEN va.heure_arrive IS NOT NULL THEN CONCAT(SUBSTR(va.heure_arrive, 1, 2), 'h', SUBSTR(va.heure_arrive, 3, 2))
                ELSE '-'
            END AS heure_arrive,
            COALESCE(vd.numero, '') AS numero_depart,
            COALESCE(cc_depart.name, '-') AS depart,
            vc_depart.nom AS depart_company,
            CASE
                WHEN vd.heure_depart IS NOT NULL THEN CONCAT(SUBSTR(vd.heure_depart, 1, 2), 'h', SUBSTR(vd.heure_depart, 3, 2))
                ELSE '-'
            END AS heure_depart,
            COALESCE(vd.date_vol, va.date_vol) AS date_vol,
            a.equipement,
            a.capacite
        FROM
            vol_arrives va
        LEFT JOIN
            vol_freres vf ON va.numero = vf.numero_arrivee
        LEFT JOIN
            vol_departs vd ON vf.numero_depart = vd.numero AND va.date_vol = vd.date_vol
        LEFT JOIN
            city_codes cc_arrivee ON va.depart = cc_arrivee.code
        LEFT JOIN
            companies vc_arrive ON va.companie_id = vc_arrive.id
        LEFT JOIN
            city_codes cc_depart ON vd.destination = cc_depart.code
        LEFT JOIN
            companies vc_depart ON vd.companie_id = vc_depart.id
        LEFT JOIN
            avions a ON va.avion_id = a.id OR vd.avion_id = a.id
        WHERE
            DATE_FORMAT(va.date_vol, '%Y-%u') = (SELECT week_year FROM ALL_VOLS)
        UNION
        SELECT
            COALESCE(va.numero, '') AS numero_arrivee,
            COALESCE(cc_arrivee.name, '-') AS arrivee,
            vc_arrive.nom AS arrivee_company,
            CASE
                WHEN va.heure_arrive IS NOT NULL THEN CONCAT(SUBSTR(va.heure_arrive, 1, 2), 'h', SUBSTR(va.heure_arrive, 3, 2))
                ELSE '-'
            END AS heure_arrive,
            COALESCE(vd.numero, '') AS numero_depart,
            COALESCE(cc_depart.name, '-') AS depart,
            vc_depart.nom AS depart_company,
            CASE
                WHEN vd.heure_depart IS NOT NULL THEN CONCAT(SUBSTR(vd.heure_depart, 1, 2), 'h', SUBSTR(vd.heure_depart, 3, 2))
                ELSE '-'
            END AS heure_depart,
            COALESCE(vd.date_vol, va.date_vol) AS date_vol,
            a.equipement,
            a.capacite
        FROM
            vol_departs vd
        LEFT JOIN
            vol_freres vf ON vd.numero = vf.numero_depart
        LEFT JOIN
            vol_arrives va ON vf.numero_arrivee = va.numero AND vd.date_vol = va.date_vol
        LEFT JOIN
            city_codes cc_arrivee ON va.depart = cc_arrivee.code
        LEFT JOIN
            companies vc_arrive ON va.companie_id = vc_arrive.id
        LEFT JOIN
            city_codes cc_depart ON vd.destination = cc_depart.code
        LEFT JOIN
            companies vc_depart ON vd.companie_id = vc_depart.id
        LEFT JOIN
            avions a ON va.avion_id = a.id OR vd.avion_id = a.id
        WHERE
            DATE_FORMAT(vd.date_vol, '%Y-%u') = (SELECT week_year FROM ALL_VOLS)
        ORDER BY
            date_vol;

        ";

        $vols = DB::select($sql);
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

    public function datatable_saisonnier()
    {


            $sql = "SELECT DISTINCT
                    COALESCE(va.numero, '') AS numero_arrivee,
                    COALESCE(cc_arrivee.name, '-') AS arrivee,
                    vc_arrive.nom AS arrivee_company,
                    CASE
                        WHEN va.heure_arrive IS NOT NULL THEN CONCAT(SUBSTR(va.heure_arrive, 1, 2), 'h', SUBSTR(va.heure_arrive, 3, 2))
                        ELSE '-'
                    END AS heure_arrive,
                    vd.numero AS numero_depart,
                    COALESCE(cc_depart.name, '-') AS depart,
                    vc_depart.nom AS depart_company,
                    CASE
                        WHEN vd.heure_depart IS NOT NULL THEN CONCAT(SUBSTR(vd.heure_depart, 1, 2), 'h', SUBSTR(vd.heure_depart, 3, 2))
                        ELSE '-'
                    END AS heure_depart,
                    vd.date_vol,
                    a.equipement,
                    a.capacite
                FROM
                    vol_departs vd
                LEFT JOIN
                    vol_freres vf ON vd.numero = vf.numero_depart
                LEFT JOIN
                    vol_arrives va ON vf.numero_arrivee = va.numero AND vd.date_vol = va.date_vol
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

                UNION ALL

                SELECT DISTINCT
                    va.numero AS numero_arrivee,
                    COALESCE(cc_arrivee.name, '-') AS arrivee,
                    vc_arrive.nom AS arrivee_company,
                    CASE
                        WHEN va.heure_arrive IS NOT NULL THEN CONCAT(SUBSTR(va.heure_arrive, 1, 2), 'h', SUBSTR(va.heure_arrive, 3, 2))
                        ELSE '-'
                    END AS heure_arrive,
                    COALESCE(vd.numero, '') AS numero_depart,
                    COALESCE(cc_depart.name, '-') AS depart,
                    vc_depart.nom AS depart_company,
                    CASE
                        WHEN vd.heure_depart IS NOT NULL THEN CONCAT(SUBSTR(vd.heure_depart, 1, 2), 'h', SUBSTR(vd.heure_depart, 3, 2))
                        ELSE '-'
                    END AS heure_depart,
                    va.date_vol,
                    a.equipement,
                    a.capacite
                FROM
                    vol_arrives va
                LEFT JOIN
                    vol_freres vf ON va.numero = vf.numero_arrivee
                LEFT JOIN
                    vol_departs vd ON vf.numero_depart = vd.numero AND va.date_vol = vd.date_vol
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

                ORDER BY
                    date_vol;
            ";

        $vols = DB::select($sql);

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
