<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class HomeControlle extends Controller
{
    public function dashbord()
    {


        return view('pages.dashboard');

    }
    public function Get_saison()
    {

        $saisons = DB::table('saisons')->get();

        return view('pages.dashboard', compact("saisons"));


    }
    public function Count_All($saison_id)
    {
        // Fetch all saisons from the database
        $saisons = DB::table('saisons')->get();
        DB::statement("SET lc_time_names = 'fr_FR'");
        $saisonId = 1; // Replace with your desired saison_id

        $Count_all = DB::table('vol_arrives')
        ->select(
            DB::raw('(SELECT COUNT(*) FROM vol_arrives WHERE saison_id = ' . $saison_id . ') as count_arrivees'),
            DB::raw('(SELECT COUNT(*) FROM vol_departs WHERE saison_id = ' . $saison_id . ') as count_departs'),
            DB::raw('
                (SELECT COUNT(*) FROM vol_arrives WHERE saison_id = ' . $saison_id . ') +
                (SELECT COUNT(*) FROM vol_departs WHERE saison_id = ' . $saison_id . ') as count_all
            ')
        )
        ->first();
        $Count_mois_charge = DB::select("WITH all_vols AS (
            SELECT
                    MONTHNAME(date_vol) AS month,
                    COUNT(*) AS count_vols
                FROM
                    (SELECT date_vol FROM vol_arrives WHERE saison_id = $saison_id
                    UNION ALL
                    SELECT date_vol FROM vol_departs WHERE saison_id = $saison_id) AS all_dates
                GROUP BY MONTHNAME(date_vol)
            ),
            arrive_counts AS (
                SELECT MONTHNAME(date_vol) AS month, COUNT(*) AS count_arrives
                FROM vol_arrives
                WHERE saison_id = $saison_id
                GROUP BY MONTHNAME(date_vol)
            ),
            depart_counts AS (
                SELECT MONTHNAME(date_vol) AS month, COUNT(*) AS count_departs
                FROM vol_departs
                WHERE saison_id = $saison_id
                GROUP BY MONTHNAME(date_vol)
            )
            SELECT
                all_vols.month AS month,
                COALESCE(arrive_counts.count_arrives, 0) AS count_arrives,
                COALESCE(depart_counts.count_departs, 0) AS count_departs,
                all_vols.count_vols AS count_vols
            FROM all_vols
            LEFT JOIN arrive_counts ON all_vols.month = arrive_counts.month
            LEFT JOIN depart_counts ON all_vols.month = depart_counts.month
            WHERE all_vols.count_vols = (
                SELECT MAX(count_vols)
                FROM all_vols
        );");

        $Count_semaine_charge = DB::select("SELECT
            DATE_FORMAT(va.date_vol, '%Y-%u') AS week_year,
            CONCAT(
                DATE_FORMAT(MIN(va.date_vol), '%d-%m'),
                ' to ',
                DATE_FORMAT(MAX(va.date_vol), '%d-%m-%Y')
            ) AS week_period,
            COUNT(DISTINCT va.id) AS count_arrivee,
            COUNT(DISTINCT vd.id) AS count_depart,
            COUNT(DISTINCT va.id) + COUNT(DISTINCT vd.id) AS count_all
            FROM vol_arrives va
            LEFT JOIN vol_departs vd ON va.date_vol = vd.date_vol AND vd.saison_id = $saison_id
            WHERE va.saison_id = $saison_id
            GROUP BY DATE_FORMAT(va.date_vol, '%Y-%u')
            ORDER BY count_all DESC, week_year DESC
            LIMIT 2;"
        );
        $Count_somaine_charts = [
            'arrivees' => 0,
            'departs' => 0,
            'total' => 0
        ];
        $Count_somaine_charts = DB::select("SELECT
                DATE_FORMAT(va.date_vol, '%Y-%u') AS week_year,
                COUNT(DISTINCT va.id) AS count_arrivee,
                COUNT(DISTINCT vd.id) AS count_depart,
                COUNT(DISTINCT va.id) + COUNT(DISTINCT vd.id) AS count_all
            FROM vol_arrives va
            LEFT JOIN vol_departs vd ON va.date_vol = vd.date_vol AND vd.saison_id = $saison_id
            WHERE va.saison_id = $saison_id
            GROUP BY DATE_FORMAT(va.date_vol, '%Y-%u')
            ORDER BY week_year DESC;"
        );

        // dd($Count_somaine_charts);
        $count_jours_charge = DB::select("WITH BusiestDays AS (
                SELECT va.date_vol, COUNT(*) AS flight_count
                FROM vol_arrives va
                WHERE va.saison_id = $saison_id
                GROUP BY va.date_vol
                ORDER BY flight_count DESC
            )
            SELECT
            DATE_FORMAT(bd.date_vol, '%W, %d/%m/%Y') AS date_vol,
                COUNT(DISTINCT va.id) AS count_arrivee,
                COUNT(DISTINCT vd.id) AS count_depart,
                COUNT(DISTINCT va.id) + COUNT(DISTINCT vd.id) AS count_all
            FROM BusiestDays bd
            LEFT JOIN vol_arrives va ON bd.date_vol = va.date_vol AND va.saison_id = $saison_id
            LEFT JOIN vol_departs vd ON bd.date_vol = vd.date_vol AND vd.saison_id = $saison_id
            GROUP BY bd.date_vol
            ORDER BY count_all DESC, bd.date_vol DESC
            LIMIT 3;"
        );

        if (!$Count_all) {
            return response()->json(['error' => 'No data found Count_all'], 404);
        }
        if (!$Count_mois_charge) {
            return response()->json(['error' => 'No data found Count_mois_charge'], 404);
        }
        if (!$Count_semaine_charge) {
            return response()->json(['error' => 'No data found Count_semaine_charge'], 404);
        }
        if (!$count_jours_charge) {
            return response()->json(['error' => 'No data found count_jours_charge'], 404);
        }

        return response()->json([
            'Count_all' => $Count_all,
            'Count_mois_charge' => $Count_mois_charge,
            'Count_semaine_charge' => $Count_semaine_charge,
            'count_jours_charge' => $count_jours_charge,
            'Count_somaine_charts' => $Count_somaine_charts
        ]);
        return view('pages.dashboard', compact("saisons"));

    }
}
