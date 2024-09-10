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

        $Count_all = DB::select("
            WITH AllFlights AS (
                SELECT date_vol, saison_id, 'arrive' AS source FROM vol_arrives
                UNION ALL
                SELECT date_vol, saison_id, 'depart' AS source FROM vol_departs
            ),
            FlightCounts AS (
                SELECT
                    saison_id,
                    COUNT(*) AS total_flights,
                    SUM(CASE WHEN source = 'arrive' THEN 1 ELSE 0 END) AS count_arrivee,
                    SUM(CASE WHEN source = 'depart' THEN 1 ELSE 0 END) AS count_depart
                FROM AllFlights
                GROUP BY saison_id
            )
            SELECT
                saison_id,
                count_arrivee,
                count_depart,
                (count_arrivee + count_depart) AS count_all
            FROM FlightCounts
            ORDER BY saison_id;
        ");

        // Initialize the array
        $array_count_all = [];

        // Loop through the result and build the array
        foreach ($Count_all as $row) {
            $array_count_all[$row->saison_id] = [
                'count_arrivee' => $row->count_arrivee,
                'count_depart' => $row->count_depart,
                'count_all' => $row->count_all
            ];
        }
        $Count_mois_chargee = DB::select("WITH all_vols AS (
            SELECT
                saison_id,
                MONTHNAME(date_vol) AS month,
                COUNT(*) AS count_vols
            FROM
                (SELECT saison_id, date_vol FROM vol_arrives
                 UNION ALL
                 SELECT saison_id, date_vol FROM vol_departs) AS all_dates
            GROUP BY saison_id, MONTHNAME(date_vol)
        ),
        arrive_counts AS (
            SELECT
                saison_id,
                MONTHNAME(date_vol) AS month,
                COUNT(*) AS count_arrives
            FROM vol_arrives
            GROUP BY saison_id, MONTHNAME(date_vol)
        ),
        depart_counts AS (
            SELECT
                saison_id,
                MONTHNAME(date_vol) AS month,
                COUNT(*) AS count_departs
            FROM vol_departs
            GROUP BY saison_id, MONTHNAME(date_vol)
        )
        SELECT
            all_vols.saison_id AS saison_id,
            all_vols.month AS month,
            COALESCE(arrive_counts.count_arrives, 0) AS count_arrives,
            COALESCE(depart_counts.count_departs, 0) AS count_departs,
            all_vols.count_vols AS count_vols
        FROM all_vols
        LEFT JOIN arrive_counts
            ON all_vols.saison_id = arrive_counts.saison_id
            AND all_vols.month = arrive_counts.month
        LEFT JOIN depart_counts
            ON all_vols.saison_id = depart_counts.saison_id
            AND all_vols.month = depart_counts.month
        WHERE all_vols.count_vols = (
            SELECT MAX(count_vols)
            FROM all_vols
            WHERE saison_id = all_vols.saison_id
        ) ");

        // Initialize the array
        $array_count_mois = [];

        // Loop through the result and build the array
        foreach ($Count_mois_chargee as $row) {
            $array_count_mois[$row->saison_id] = [
                'month'=>$row->month,
                'count_arrivee' => $row->count_arrives,
                'count_depart' => $row->count_departs,
                'count_all' => $row->count_vols
            ];
        }

        $Count_semaine_chargee = DB::select("SELECT
                week_year,
                saison_id,
                periode_start,
                periode_end,
                total_flights,
                arrivee_count,
                depart_count
            FROM (
                SELECT
                    DATE_FORMAT(date_vol, '%Y-%u') AS week_year,
                    saison_id,
                    COUNT(CASE WHEN source = 'arrive' THEN 1 END) AS arrivee_count,
                    COUNT(CASE WHEN source = 'depart' THEN 1 END) AS depart_count,
                    COUNT(*) AS total_flights,
                    MIN(date_vol) AS periode_start,
                    MAX(date_vol) AS periode_end
                FROM (
                    SELECT date_vol, saison_id, 'arrive' AS source FROM vol_arrives
                    UNION ALL
                    SELECT date_vol, saison_id, 'depart' AS source FROM vol_departs
                ) AS all_flights
                GROUP BY week_year, saison_id
            ) AS FlightData
            WHERE (saison_id, total_flights) IN (
                SELECT saison_id, total_flights
                FROM (
                    SELECT
                        saison_id,
                        week_year,
                        total_flights,
                        RANK() OVER (PARTITION BY saison_id ORDER BY total_flights DESC) AS rank
                    FROM (
                        SELECT
                            DATE_FORMAT(date_vol, '%Y-%u') AS week_year,
                            saison_id,
                            COUNT(*) AS total_flights
                        FROM (
                            SELECT date_vol, saison_id, 'arrive' AS source FROM vol_arrives
                            UNION ALL
                            SELECT date_vol, saison_id, 'depart' AS source FROM vol_departs
                        ) AS all_flights
                        GROUP BY week_year, saison_id
                    ) AS subquery
                ) AS ranked_weeks
                WHERE rank <= 2
            )
            ORDER BY  week_year DESC ;"
        );

                // Initialize the array
        $array_count_semaine = [];

        // Loop through the result and build the array
        foreach ($Count_semaine_chargee as $row) {
            $array_count_semaine[$row->saison_id] = [
                'week_year'=>$row->week_year,
                'periode' => $row->periode_start . ' - ' . $row->periode_end,
                'arrivee_count' => $row->arrivee_count,
                'depart_count' => $row->depart_count,
                'total_flights' => $row->total_flights
            ];
        }
        $Count_jeurs_chargee = DB::select("WITH RankedDays AS (
            SELECT
                date_vol,
                saison_id,
                COUNT(*) AS flight_count,
                ROW_NUMBER() OVER (PARTITION BY saison_id ORDER BY COUNT(*) DESC) AS row_num
            FROM (
                SELECT date_vol, saison_id FROM vol_arrives
                UNION ALL
                SELECT date_vol, saison_id FROM vol_departs
            ) AS all_flights
            GROUP BY date_vol, saison_id
        )
        SELECT
            DATE_FORMAT(rd.date_vol, '%W, %d/%m/%Y') AS date_vol,
            rd.saison_id,
            SUM(CASE WHEN source = 'arrive' THEN 1 ELSE 0 END) AS count_arrivee,
            SUM(CASE WHEN source = 'depart' THEN 1 ELSE 0 END) AS count_depart,
            COUNT(*) AS count_all
        FROM RankedDays rd
        LEFT JOIN (
            SELECT date_vol, saison_id, 'arrive' AS source FROM vol_arrives
            UNION ALL
            SELECT date_vol, saison_id, 'depart' AS source FROM vol_departs
        ) AS all_flights ON rd.date_vol = all_flights.date_vol AND rd.saison_id = all_flights.saison_id
        WHERE rd.row_num <= 3  -- Limit to top 3 days for each saison_id
        GROUP BY rd.date_vol, rd.saison_id
        ORDER BY rd.saison_id, rd.date_vol DESC;
        ");

        // Initialize the array
        $array_count_jeurs = [];

        // Loop through the result and build the array
        foreach ($Count_jeurs_chargee as $row) {
            $array_count_jeurs[$row->saison_id] = [
                'date_vol'=>$row->date_vol,
                'count_arrivee' => $row->count_arrivee,
                'count_depart' => $row->count_depart,
                'count_all' => $row->count_all
            ];
        }




        return view('pages.dashboard', compact('saisons','array_count_all','array_count_mois','array_count_semaine','array_count_jeurs'));


    }

}
