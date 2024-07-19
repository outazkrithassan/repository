<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class HomeControlle extends Controller
{
    public function dashbord()
    {
        return view("pages.dashboard");
    }
    public function Count_All()
    {
        DB::statement("SET lc_time_names = 'fr_FR'");
        $Count_all = DB::table('vol_arrives')
        ->select(
            DB::raw('(SELECT COUNT(*) FROM vol_arrives) as count_arrivees'),
            DB::raw('(SELECT COUNT(*) FROM vol_departs) as count_departs'),
            DB::raw('
                (SELECT COUNT(*) FROM vol_arrives) + (SELECT COUNT(*) FROM vol_departs) as count_all
            ')
        )
        ->first();
        $Count_mois_charge= DB::select("WITH all_vols AS (
            SELECT
                MONTHNAME(date_vol) AS month,

                COUNT(*) AS count_vols
            FROM
                (
                    SELECT
                        date_vol
                    FROM
                        vol_arrives
                    UNION ALL
                    SELECT
                        date_vol
                    FROM
                        vol_departs
                ) AS all_dates
            GROUP BY
                MONTHNAME(date_vol)
        ),
        arrive_counts AS (
            SELECT
                MONTHNAME(date_vol) AS month,
                COUNT(*) AS count_arrives
            FROM
                vol_arrives
            GROUP BY
                MONTHNAME(date_vol)
        ),
        depart_counts AS (
            SELECT
                MONTHNAME(date_vol) AS month,
                COUNT(*) AS count_departs
            FROM
                vol_departs
            GROUP BY
                MONTHNAME(date_vol)
        )
        SELECT
            all_vols.month AS month,
            COALESCE(arrive_counts.count_arrives, 0) AS count_arrives,
            COALESCE(depart_counts.count_departs, 0) AS count_departs,
            all_vols.count_vols AS count_vols
        FROM
            all_vols
        LEFT JOIN
            arrive_counts ON all_vols.month = arrive_counts.month
        LEFT JOIN
            depart_counts ON all_vols.month = depart_counts.month
        WHERE
            all_vols.count_vols = (
                SELECT MAX(count_vols)
                FROM all_vols

            );");

        // DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''))");
        $Count_semaine_charge =DB::select("SELECT
                DATE_FORMAT(va.date_vol, '%Y-%u') AS week_year,
                CONCAT(
                    DATE_FORMAT(MIN(va.date_vol), '%d-%m'),
                    ' to ',
                    DATE_FORMAT(MAX(va.date_vol), '%d-%m-%Y')
                ) AS week_period,
                COUNT(DISTINCT va.id) AS count_arrivee,
                COUNT(DISTINCT vd.id) AS count_depart,
                COUNT(DISTINCT va.id) + COUNT(DISTINCT vd.id) AS count_all
            FROM
                vol_arrives va
            LEFT JOIN
                vol_departs vd ON va.date_vol = vd.date_vol
            GROUP BY
                DATE_FORMAT(va.date_vol, '%Y-%u')
            ORDER BY
                count_all DESC, week_year DESC
            LIMIT 2;

        ");
        $Count_somaine_charts =DB::select("SELECT
                DATE_FORMAT(va.date_vol, '%Y-%u') AS week_year,
                COUNT(DISTINCT va.id) AS count_arrivee,
                COUNT(DISTINCT vd.id) AS count_depart,
                COUNT(DISTINCT va.id) + COUNT(DISTINCT vd.id) AS count_all
            FROM
                vol_arrives va
            LEFT JOIN
                vol_departs vd ON va.date_vol = vd.date_vol
            GROUP BY
                DATE_FORMAT(va.date_vol, '%Y-%u')
            ORDER BY
                week_year DESC;

        ");
        // dd($Count_somaine_charts);
        $count_jours_charge=DB::select("WITH BusiestDays AS (
            SELECT
                va.date_vol,
                COUNT(*) AS flight_count
            FROM
                vol_arrives va
            GROUP BY
                va.date_vol
            ORDER BY
                flight_count DESC

        )
        SELECT
            bd.date_vol,
            COUNT(DISTINCT va.id) AS count_arrivee,
            COUNT(DISTINCT vd.id) AS count_depart,
            COUNT(DISTINCT va.id) + COUNT(DISTINCT vd.id) AS count_all
        FROM
            BusiestDays bd
        LEFT JOIN
            vol_arrives va ON bd.date_vol = va.date_vol
        LEFT JOIN
            vol_departs vd ON bd.date_vol = vd.date_vol
        GROUP BY
            bd.date_vol
        ORDER BY
            count_all DESC,bd.date_vol DESC
        limit 3
        ");

        return view('pages.dashboard', compact("Count_all","Count_mois_charge","count_jours_charge","Count_semaine_charge","Count_somaine_charts"));

    }
}
