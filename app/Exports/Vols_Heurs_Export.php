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


class Vols_Heurs_Export implements FromCollection, WithHeadings, WithStyles, WithEvents,WithCustomStartCell
{
    /**
    * @return \Illuminate\Support\Collection
    */
    // public function collection()
    // {
    //     $query = "WITH TopWeek AS (
    //             SELECT
    //                 DATE_FORMAT(va.date_vol, '%Y-%u') AS week_year,
    //                 CONCAT(
    //                     DATE_FORMAT(MIN(va.date_vol), '%d-%m'),
    //                     ' to ',
    //                     DATE_FORMAT(MAX(va.date_vol), '%d-%m-%Y')
    //                 ) AS week_period,
    //                 COUNT(DISTINCT va.id) AS count_arrivee,
    //                 COUNT(DISTINCT vd.id) AS count_depart,
    //                 COUNT(DISTINCT va.id) + COUNT(DISTINCT vd.id) AS count_all,
    //                 MIN(va.date_vol) AS start_date,
    //                 MAX(va.date_vol) AS end_date
    //             FROM
    //                 vol_arrives va
    //             LEFT JOIN
    //                 vol_departs vd ON va.date_vol = vd.date_vol
    //             GROUP BY
    //                 DATE_FORMAT(va.date_vol, '%Y-%u')
    //             ORDER BY
    //                 count_all DESC, week_year DESC
    //             LIMIT 1
    //         ),
    //         FlightData AS (
    //             SELECT
    //                 DATE(vd.date_vol) AS flight_date,
    //                 'Départ' AS flight_type,
    //                 HOUR(STR_TO_DATE(vd.heure_depart, '%H%i')) AS flight_hour,
    //                 COUNT(*) AS flight_count
    //             FROM
    //                 vol_departs vd
    //             WHERE
    //                 DATE(vd.date_vol) BETWEEN (SELECT start_date FROM TopWeek) AND (SELECT end_date FROM TopWeek)
    //             GROUP BY
    //                 flight_date, flight_type, flight_hour

    //             UNION ALL

    //             SELECT
    //                 DATE(va.date_vol) AS flight_date,
    //                 'Arrivée' AS flight_type,
    //                 HOUR(STR_TO_DATE(va.heure_arrive, '%H%i')) AS flight_hour,
    //                 COUNT(*) AS flight_count
    //             FROM
    //                 vol_arrives va
    //             WHERE
    //                 DATE(va.date_vol) BETWEEN (SELECT start_date FROM TopWeek) AND (SELECT end_date FROM TopWeek)
    //             GROUP BY
    //                 flight_date, flight_type, flight_hour
    //         ),
    //         Summary AS (
    //             SELECT
    //                 flight_date,
    //                 flight_type,
    //                 SUM(CASE WHEN flight_hour = 0 THEN flight_count ELSE 0 END) AS `00h à 01h`,
    //                 SUM(CASE WHEN flight_hour = 1 THEN flight_count ELSE 0 END) AS `01h à 02h`,
    //                 SUM(CASE WHEN flight_hour = 2 THEN flight_count ELSE 0 END) AS `02h à 03h`,
    //                 SUM(CASE WHEN flight_hour = 3 THEN flight_count ELSE 0 END) AS `03h à 04h`,
    //                 SUM(CASE WHEN flight_hour = 4 THEN flight_count ELSE 0 END) AS `04h à 05h`,
    //                 SUM(CASE WHEN flight_hour = 5 THEN flight_count ELSE 0 END) AS `05h à 06h`,
    //                 SUM(CASE WHEN flight_hour = 6 THEN flight_count ELSE 0 END) AS `06h à 07h`,
    //                 SUM(CASE WHEN flight_hour = 7 THEN flight_count ELSE 0 END) AS `07h à 08h`,
    //                 SUM(CASE WHEN flight_hour = 8 THEN flight_count ELSE 0 END) AS `08h à 09h`,
    //                 SUM(CASE WHEN flight_hour = 9 THEN flight_count ELSE 0 END) AS `09h à 10h`,
    //                 SUM(CASE WHEN flight_hour = 10 THEN flight_count ELSE 0 END) AS `10h à 11h`,
    //                 SUM(CASE WHEN flight_hour = 11 THEN flight_count ELSE 0 END) AS `11h à 12h`,
    //                 SUM(CASE WHEN flight_hour = 12 THEN flight_count ELSE 0 END) AS `12h à 13h`,
    //                 SUM(CASE WHEN flight_hour = 13 THEN flight_count ELSE 0 END) AS `13h à 14h`,
    //                 SUM(CASE WHEN flight_hour = 14 THEN flight_count ELSE 0 END) AS `14h à 15h`,
    //                 SUM(CASE WHEN flight_hour = 15 THEN flight_count ELSE 0 END) AS `15h à 16h`,
    //                 SUM(CASE WHEN flight_hour = 16 THEN flight_count ELSE 0 END) AS `16h à 17h`,
    //                 SUM(CASE WHEN flight_hour = 17 THEN flight_count ELSE 0 END) AS `17h à 18h`,
    //                 SUM(CASE WHEN flight_hour = 18 THEN flight_count ELSE 0 END) AS `18h à 19h`,
    //                 SUM(CASE WHEN flight_hour = 19 THEN flight_count ELSE 0 END) AS `19h à 20h`,
    //                 SUM(CASE WHEN flight_hour = 20 THEN flight_count ELSE 0 END) AS `20h à 21h`,
    //                 SUM(CASE WHEN flight_hour = 21 THEN flight_count ELSE 0 END) AS `21h à 22h`,
    //                 SUM(CASE WHEN flight_hour = 22 THEN flight_count ELSE 0 END) AS `22h à 23h`,
    //                 SUM(CASE WHEN flight_hour = 23 THEN flight_count ELSE 0 END) AS `23h à 00h`
    //             FROM
    //                 FlightData
    //             GROUP BY
    //                 flight_date, flight_type
    //         )
    //         SELECT
    //             flight_date,
    //             flight_type,
    //             `00h à 01h`,
    //             `01h à 02h`,
    //             `02h à 03h`,
    //             `03h à 04h`,
    //             `04h à 05h`,
    //             `05h à 06h`,
    //             `06h à 07h`,
    //             `07h à 08h`,
    //             `08h à 09h`,
    //             `09h à 10h`,
    //             `10h à 11h`,
    //             `11h à 12h`,
    //             `12h à 13h`,
    //             `13h à 14h`,
    //             `14h à 15h`,
    //             `15h à 16h`,
    //             `16h à 17h`,
    //             `17h à 18h`,
    //             `18h à 19h`,
    //             `19h à 20h`,
    //             `20h à 21h`,
    //             `21h à 22h`,
    //             `22h à 23h`,
    //             `23h à 00h`


    //         FROM
    //             Summary
    //         ORDER BY
    //             flight_date, flight_type;
    //     ";

    //     return collect(DB::select($query));
    // }
    // public function collection()
    // {
    //     $sql_arrive = "
    //     WITH TopWeek AS (
    //         SELECT
    //             DATE_FORMAT(va.date_vol, '%Y-%u') AS week_year,
    //             CONCAT(
    //                 DATE_FORMAT(MIN(va.date_vol), '%d-%m'),
    //                 ' to ',
    //                 DATE_FORMAT(MAX(va.date_vol), '%d-%m-%Y')
    //             ) AS week_period,
    //             COUNT(DISTINCT va.id) AS count_arrivee,
    //             COUNT(DISTINCT vd.id) AS count_depart,
    //             COUNT(DISTINCT va.id) + COUNT(DISTINCT vd.id) AS count_all,
    //             MIN(va.date_vol) AS start_date,
    //             MAX(va.date_vol) AS end_date
    //         FROM
    //             vol_arrives va
    //         LEFT JOIN
    //             vol_departs vd ON va.date_vol = vd.date_vol
    //         GROUP BY
    //             DATE_FORMAT(va.date_vol, '%Y-%u')
    //         ORDER BY
    //             count_all DESC, week_year DESC
    //         LIMIT 1
    //     ),
    //     FlightData AS (
    //         SELECT
    //             DATE(va.date_vol) AS flight_date,
    //             'Arrivée' AS flight_type,
    //             HOUR(STR_TO_DATE(va.heure_arrive, '%H%i')) AS flight_hour,
    //             COUNT(*) AS flight_count
    //         FROM
    //             vol_arrives va
    //         WHERE
    //             DATE(va.date_vol) BETWEEN (SELECT start_date FROM TopWeek) AND (SELECT end_date FROM TopWeek)
    //         GROUP BY
    //             flight_date, flight_type, flight_hour
    //     ),
    //     Summary AS (
    //         SELECT
    //             flight_date,
    //             flight_type,
    //             SUM(CASE WHEN flight_hour = 0 THEN flight_count ELSE 0 END) AS `00h à 01h`,
    //             SUM(CASE WHEN flight_hour = 1 THEN flight_count ELSE 0 END) AS `01h à 02h`,
    //             SUM(CASE WHEN flight_hour = 2 THEN flight_count ELSE 0 END) AS `02h à 03h`,
    //             SUM(CASE WHEN flight_hour = 3 THEN flight_count ELSE 0 END) AS `03h à 04h`,
    //             SUM(CASE WHEN flight_hour = 4 THEN flight_count ELSE 0 END) AS `04h à 05h`,
    //             SUM(CASE WHEN flight_hour = 5 THEN flight_count ELSE 0 END) AS `05h à 06h`,
    //             SUM(CASE WHEN flight_hour = 6 THEN flight_count ELSE 0 END) AS `06h à 07h`,
    //             SUM(CASE WHEN flight_hour = 7 THEN flight_count ELSE 0 END) AS `07h à 08h`,
    //             SUM(CASE WHEN flight_hour = 8 THEN flight_count ELSE 0 END) AS `08h à 09h`,
    //             SUM(CASE WHEN flight_hour = 9 THEN flight_count ELSE 0 END) AS `09h à 10h`,
    //             SUM(CASE WHEN flight_hour = 10 THEN flight_count ELSE 0 END) AS `10h à 11h`,
    //             SUM(CASE WHEN flight_hour = 11 THEN flight_count ELSE 0 END) AS `11h à 12h`,
    //             SUM(CASE WHEN flight_hour = 12 THEN flight_count ELSE 0 END) AS `12h à 13h`,
    //             SUM(CASE WHEN flight_hour = 13 THEN flight_count ELSE 0 END) AS `13h à 14h`,
    //             SUM(CASE WHEN flight_hour = 14 THEN flight_count ELSE 0 END) AS `14h à 15h`,
    //             SUM(CASE WHEN flight_hour = 15 THEN flight_count ELSE 0 END) AS `15h à 16h`,
    //             SUM(CASE WHEN flight_hour = 16 THEN flight_count ELSE 0 END) AS `16h à 17h`,
    //             SUM(CASE WHEN flight_hour = 17 THEN flight_count ELSE 0 END) AS `17h à 18h`,
    //             SUM(CASE WHEN flight_hour = 18 THEN flight_count ELSE 0 END) AS `18h à 19h`,
    //             SUM(CASE WHEN flight_hour = 19 THEN flight_count ELSE 0 END) AS `19h à 20h`,
    //             SUM(CASE WHEN flight_hour = 20 THEN flight_count ELSE 0 END) AS `20h à 21h`,
    //             SUM(CASE WHEN flight_hour = 21 THEN flight_count ELSE 0 END) AS `21h à 22h`,
    //             SUM(CASE WHEN flight_hour = 22 THEN flight_count ELSE 0 END) AS `22h à 23h`,
    //             SUM(CASE WHEN flight_hour = 23 THEN flight_count ELSE 0 END) AS `23h à 00h`
    //         FROM
    //             FlightData
    //         GROUP BY
    //             flight_date, flight_type
    //     )
    //     SELECT
    //         flight_date,
    //         flight_type,
    //         `00h à 01h`,
    //         `01h à 02h`,
    //         `02h à 03h`,
    //         `03h à 04h`,
    //         `04h à 05h`,
    //         `05h à 06h`,
    //         `06h à 07h`,
    //         `07h à 08h`,
    //         `08h à 09h`,
    //         `09h à 10h`,
    //         `10h à 11h`,
    //         `11h à 12h`,
    //         `12h à 13h`,
    //         `13h à 14h`,
    //         `14h à 15h`,
    //         `15h à 16h`,
    //         `16h à 17h`,
    //         `17h à 18h`,
    //         `18h à 19h`,
    //         `19h à 20h`,
    //         `20h à 21h`,
    //         `21h à 22h`,
    //         `22h à 23h`,
    //         `23h à 00h`
    //     FROM
    //         Summary
    //     ORDER BY
    //         flight_date, flight_type;
    //     ";

    //     $sql_depart = "
    //     WITH TopWeek AS (
    //         SELECT
    //             DATE_FORMAT(vd.date_vol, '%Y-%u') AS week_year,
    //             CONCAT(
    //                 DATE_FORMAT(MIN(vd.date_vol), '%d-%m'),
    //                 ' to ',
    //                 DATE_FORMAT(MAX(vd.date_vol), '%d-%m-%Y')
    //             ) AS week_period,
    //             COUNT(DISTINCT vd.id) AS count_depart,
    //             COUNT(DISTINCT va.id) AS count_arrivee,
    //             COUNT(DISTINCT vd.id) + COUNT(DISTINCT va.id) AS count_all,
    //             MIN(vd.date_vol) AS start_date,
    //             MAX(vd.date_vol) AS end_date
    //         FROM
    //             vol_departs vd
    //         LEFT JOIN
    //             vol_arrives va ON vd.date_vol = va.date_vol
    //         GROUP BY
    //             DATE_FORMAT(vd.date_vol, '%Y-%u')
    //         ORDER BY
    //             count_all DESC, week_year DESC
    //         LIMIT 1
    //     ),
    //     FlightData AS (
    //         SELECT
    //             DATE(vd.date_vol) AS flight_date,
    //             'Départ' AS flight_type,
    //             HOUR(STR_TO_DATE(vd.heure_depart, '%H%i')) AS flight_hour,
    //             COUNT(*) AS flight_count
    //         FROM
    //             vol_departs vd
    //         WHERE
    //             DATE(vd.date_vol) BETWEEN (SELECT start_date FROM TopWeek) AND (SELECT end_date FROM TopWeek)
    //         GROUP BY
    //             flight_date, flight_type, flight_hour
    //     ),
    //     Summary AS (
    //         SELECT
    //             flight_date,
    //             flight_type,
    //             SUM(CASE WHEN flight_hour = 0 THEN flight_count ELSE 0 END) AS `00h à 01h`,
    //             SUM(CASE WHEN flight_hour = 1 THEN flight_count ELSE 0 END) AS `01h à 02h`,
    //             SUM(CASE WHEN flight_hour = 2 THEN flight_count ELSE 0 END) AS `02h à 03h`,
    //             SUM(CASE WHEN flight_hour = 3 THEN flight_count ELSE 0 END) AS `03h à 04h`,
    //             SUM(CASE WHEN flight_hour = 4 THEN flight_count ELSE 0 END) AS `04h à 05h`,
    //             SUM(CASE WHEN flight_hour = 5 THEN flight_count ELSE 0 END) AS `05h à 06h`,
    //             SUM(CASE WHEN flight_hour = 6 THEN flight_count ELSE 0 END) AS `06h à 07h`,
    //             SUM(CASE WHEN flight_hour = 7 THEN flight_count ELSE 0 END) AS `07h à 08h`,
    //             SUM(CASE WHEN flight_hour = 8 THEN flight_count ELSE 0 END) AS `08h à 09h`,
    //             SUM(CASE WHEN flight_hour = 9 THEN flight_count ELSE 0 END) AS `09h à 10h`,
    //             SUM(CASE WHEN flight_hour = 10 THEN flight_count ELSE 0 END) AS `10h à 11h`,
    //             SUM(CASE WHEN flight_hour = 11 THEN flight_count ELSE 0 END) AS `11h à 12h`,
    //             SUM(CASE WHEN flight_hour = 12 THEN flight_count ELSE 0 END) AS `12h à 13h`,
    //             SUM(CASE WHEN flight_hour = 13 THEN flight_count ELSE 0 END) AS `13h à 14h`,
    //             SUM(CASE WHEN flight_hour = 14 THEN flight_count ELSE 0 END) AS `14h à 15h`,
    //             SUM(CASE WHEN flight_hour = 15 THEN flight_count ELSE 0 END) AS `15h à 16h`,
    //             SUM(CASE WHEN flight_hour = 16 THEN flight_count ELSE 0 END) AS `16h à 17h`,
    //             SUM(CASE WHEN flight_hour = 17 THEN flight_count ELSE 0 END) AS `17h à 18h`,
    //             SUM(CASE WHEN flight_hour = 18 THEN flight_count ELSE 0 END) AS `18h à 19h`,
    //             SUM(CASE WHEN flight_hour = 19 THEN flight_count ELSE 0 END) AS `19h à 20h`,
    //             SUM(CASE WHEN flight_hour = 20 THEN flight_count ELSE 0 END) AS `20h à 21h`,
    //             SUM(CASE WHEN flight_hour = 21 THEN flight_count ELSE 0 END) AS `21h à 22h`,
    //             SUM(CASE WHEN flight_hour = 22 THEN flight_count ELSE 0 END) AS `22h à 23h`,
    //             SUM(CASE WHEN flight_hour = 23 THEN flight_count ELSE 0 END) AS `23h à 00h`
    //         FROM
    //             FlightData
    //         GROUP BY
    //             flight_date, flight_type
    //     )
    //     SELECT
    //         flight_date,
    //         flight_type,
    //         `00h à 01h`,
    //         `01h à 02h`,
    //         `02h à 03h`,
    //         `03h à 04h`,
    //         `04h à 05h`,
    //         `05h à 06h`,
    //         `06h à 07h`,
    //         `07h à 08h`,
    //         `08h à 09h`,
    //         `09h à 10h`,
    //         `10h à 11h`,
    //         `11h à 12h`,
    //         `12h à 13h`,
    //         `13h à 14h`,
    //         `14h à 15h`,
    //         `15h à 16h`,
    //         `16h à 17h`,
    //         `17h à 18h`,
    //         `18h à 19h`,
    //         `19h à 20h`,
    //         `20h à 21h`,
    //         `21h à 22h`,
    //         `22h à 23h`,
    //         `23h à 00h`
    //     FROM
    //         Summary
    //     ORDER BY
    //         flight_date, flight_type;
    //     ";

    //     // Execute the queries and collect results
    //     $arrivals = collect(DB::select($sql_arrive));
    //     $departures = collect(DB::select($sql_depart));

    //     // Combine the results if needed
    //     // For example, to show both arrivals and departures in a single view
    //     $combined_results = [
    //         'arrive' => $arrivals,
    //         'depart' => $departures
    //     ];
    //     echo "<pre>";
    //         print_r($combined_results);
    //     echo "</pre>";
    //     die;


    // }




    // echo "<pre>";
    //         print_r(DB::select($query));
    // echo"</pre>";
    // die;


    protected $selectedSeason;

    public function __construct($selectedSeason)
    {
        $this->selectedSeason = $selectedSeason;
    }
    public function collection()
    {
        $selectedSeason = $this->selectedSeason;
        // Step 1: Get the top week data
        $topWeek = DB::table('vol_arrives as va')
            ->leftJoin('vol_departs as vd', 'va.date_vol', '=', 'vd.date_vol')
            ->selectRaw("
                DATE_FORMAT(va.date_vol, '%Y-%u') AS week_year,
                CONCAT(
                    DATE_FORMAT(MIN(va.date_vol), '%d-%m'),
                    ' to ',
                    DATE_FORMAT(MAX(va.date_vol), '%d-%m-%Y')
                ) AS week_period,
                COUNT(DISTINCT va.id) AS count_arrivee,
                COUNT(DISTINCT vd.id) AS count_depart,
                COUNT(DISTINCT va.id) + COUNT(DISTINCT vd.id) AS count_all,
                MIN(va.date_vol) AS start_date,
                MAX(va.date_vol) AS end_date
            ")
            ->where('va.saison_id',$selectedSeason)
            ->groupBy(DB::raw("DATE_FORMAT(va.date_vol, '%Y-%u')"))
            ->orderBy('count_all', 'DESC')
            ->orderBy('week_year', 'DESC')
            ->limit(1)
            ->first();

        if (!$topWeek) {
            return collect(); // Return an empty collection if no top week is found
        }

        // Step 2: Get flight data within the top week
        $flightData = DB::table(DB::raw('(' . $this->getFlightDataQuery($topWeek) . ') as FlightData'))
            ->selectRaw("
                flight_date,
                flight_type,
                SUM(CASE WHEN flight_hour = 0 THEN flight_count ELSE 0 END) AS `00h à 01h`,
                SUM(CASE WHEN flight_hour = 1 THEN flight_count ELSE 0 END) AS `01h à 02h`,
                SUM(CASE WHEN flight_hour = 2 THEN flight_count ELSE 0 END) AS `02h à 03h`,
                SUM(CASE WHEN flight_hour = 3 THEN flight_count ELSE 0 END) AS `03h à 04h`,
                SUM(CASE WHEN flight_hour = 4 THEN flight_count ELSE 0 END) AS `04h à 05h`,
                SUM(CASE WHEN flight_hour = 5 THEN flight_count ELSE 0 END) AS `05h à 06h`,
                SUM(CASE WHEN flight_hour = 6 THEN flight_count ELSE 0 END) AS `06h à 07h`,
                SUM(CASE WHEN flight_hour = 7 THEN flight_count ELSE 0 END) AS `07h à 08h`,
                SUM(CASE WHEN flight_hour = 8 THEN flight_count ELSE 0 END) AS `08h à 09h`,
                SUM(CASE WHEN flight_hour = 9 THEN flight_count ELSE 0 END) AS `09h à 10h`,
                SUM(CASE WHEN flight_hour = 10 THEN flight_count ELSE 0 END) AS `10h à 11h`,
                SUM(CASE WHEN flight_hour = 11 THEN flight_count ELSE 0 END) AS `11h à 12h`,
                SUM(CASE WHEN flight_hour = 12 THEN flight_count ELSE 0 END) AS `12h à 13h`,
                SUM(CASE WHEN flight_hour = 13 THEN flight_count ELSE 0 END) AS `13h à 14h`,
                SUM(CASE WHEN flight_hour = 14 THEN flight_count ELSE 0 END) AS `14h à 15h`,
                SUM(CASE WHEN flight_hour = 15 THEN flight_count ELSE 0 END) AS `15h à 16h`,
                SUM(CASE WHEN flight_hour = 16 THEN flight_count ELSE 0 END) AS `16h à 17h`,
                SUM(CASE WHEN flight_hour = 17 THEN flight_count ELSE 0 END) AS `17h à 18h`,
                SUM(CASE WHEN flight_hour = 18 THEN flight_count ELSE 0 END) AS `18h à 19h`,
                SUM(CASE WHEN flight_hour = 19 THEN flight_count ELSE 0 END) AS `19h à 20h`,
                SUM(CASE WHEN flight_hour = 20 THEN flight_count ELSE 0 END) AS `20h à 21h`,
                SUM(CASE WHEN flight_hour = 21 THEN flight_count ELSE 0 END) AS `21h à 22h`,
                SUM(CASE WHEN flight_hour = 22 THEN flight_count ELSE 0 END) AS `22h à 23h`,
                SUM(CASE WHEN flight_hour = 23 THEN flight_count ELSE 0 END) AS `23h à 00h`
            ")

            ->groupBy('flight_date', 'flight_type')
            ->get();

        return collect($flightData);
    }

    protected function getFlightDataQuery($topWeek)
    {
        $selectedSeason = $this->selectedSeason;
        return "
            SELECT
                DATE(vd.date_vol) AS flight_date,
                'Départ' AS flight_type,
                HOUR(STR_TO_DATE(vd.heure_depart, '%H%i')) AS flight_hour,
                COUNT(*) AS flight_count
            FROM
                vol_departs vd
            WHERE
                DATE(vd.date_vol) BETWEEN '{$topWeek->start_date}' AND '{$topWeek->end_date}'
                AND vd.saison_id = $selectedSeason
            GROUP BY
                flight_date, flight_type, flight_hour
            UNION ALL
            SELECT
                DATE(va.date_vol) AS flight_date,
                'Arrivée' AS flight_type,
                HOUR(STR_TO_DATE(va.heure_arrive, '%H%i')) AS flight_hour,
                COUNT(*) AS flight_count
            FROM
                vol_arrives va
            WHERE
                DATE(va.date_vol) BETWEEN '{$topWeek->start_date}' AND '{$topWeek->end_date}'
                AND va.saison_id = $selectedSeason
            GROUP BY
                flight_date, flight_type, flight_hour
        ";
    }




    public function headings(): array
    {
        return [
                'date',
                'type_flight',
                '00h à 01h',
                '01h à 02h',
                '02h à 03h',
                '03h à 04h',
                '04h à 05h',
                '05h à 06h',
                '06h à 07h',
                '07h à 08h',
                '08h à 09h',
                '09h à 10h',
                '10h à 11h',
                '11h à 12h',
                '12h à 13h',
                '13h à 14h',
                '14h à 15h',
                '15h à 16h',
                '16h à 17h',
                '17h à 18h',
                '18h à 19h',
                '19h à 20h',
                '20h à 21h',
                '21h à 22h',
                '22h à 23h',
                '23h à 00h',
                'Total'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A6:B'.$sheet->getHighestRow())
        ->getBorders()
        ->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A6:B' . $sheet->getHighestRow())->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle('B6:B' . $sheet->getHighestRow())->getFont()->setItalic(true);
        $sheet->getStyle('A6:A' . $sheet->getHighestRow())->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A6:A' . $sheet->getHighestRow())->getAlignment()->setVertical(Alignment::HORIZONTAL_CENTER);
        // $sheet->getStyle('B6:B' . $sheet->getHighestRow())->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('B6:B' . $sheet->getHighestRow())->getAlignment()->setVertical(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C5:AA5')->applyFromArray([
            'font' => [
                'bold' => true,
                'size'=>12,
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
        $sheet->setCellValue('A5', '');
        $sheet->setCellValue('B5', '');
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(20);
        return [ ];
    }
    public function startCell(): string
    {
        return 'A5';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->mergeCells('A1:AA1');
                $sheet->setCellValue('A1', 'Statistiques Vols Semaine type');
                $sheet->getStyle('A1:AA1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],]);
                // Merge title cells

                $sheet->mergeCells('A2:AA2');
                $sheet->setCellValue('A2', 'Aéroport AGADIR AL MASSIRA');
                $sheet->getStyle('A2:AA2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],]);
                $sheet->mergeCells('A3:AA3');
                $sheet->setCellValue('A3', 'Horaires en GMT+1');
                $sheet->getStyle('A3:AA3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],]);

                // Set background color for the header rows
                $sheet->getStyle('A1:AA3')->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['argb' => 'FFCCCCFF'],
                    ],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);
                $sheet->getStyle('C6:AA'.$sheet->getHighestRow())
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('C6:AA' . $sheet->getHighestRow())->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('C6:AA' . $sheet->getHighestRow())->getAlignment()->setVertical(Alignment::HORIZONTAL_CENTER);

                // Conditional formatting
                foreach (range(6, 19) as $row) {
                    foreach (range('C', 'Z') as $col) {
                        $cell = $sheet->getCell($col . $row);
                        $value = $cell->getValue();
                        if ($value <= 3) {
                            $color = '75da7e';
                        } elseif ($value >3 && $value < 6) {
                            $color = 'cccc00';
                        } elseif ($value >= 6) {
                            $color = 'cc0000';
                        }
                        $sheet->getStyle($col . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'color' => ['argb' => $color],
                            ],
                        ]);
                    }
                }
                $sheet->getColumnDimension('A')->setWidth('15');
                $sheet->getColumnDimension('A')->setAutoSize(false);
                $sheet->getColumnDimension('B')->setWidth('10');
                $sheet->getColumnDimension('B')->setAutoSize(false);
                foreach (range('C', 'Z') as $columnID) {
                    $sheet->getColumnDimension($columnID)->setAutoSize(true);
                }
                $sheet->getStyle("C5:Z5")->getAlignment()->setTextRotation(-90);



                //range cell date
                $flights = $this->collection();

                // Start row for data (adjust if needed)
                $startRow = 6;
                $currentDay = null;
                $dayStartRow = $startRow;

                // Loop through data rows
                foreach ($flights as $index => $flight) {
                    $currentRow = $startRow + $index;

                    // Check if the day has changed
                    if ($currentDay !== null && $currentDay !== $flight->flight_date) {
                        // Merge cells for the previous day
                        if ($currentRow - 1 > $dayStartRow) {
                            $sheet->mergeCells("A{$dayStartRow}:A" . ($currentRow - 1));
                        }
                        $dayStartRow = $currentRow;
                    }
                    $currentDay = $flight->flight_date;
                }

                // Merge cells for the last day
                $highestRow = $sheet->getHighestRow();

                if ($highestRow >= $dayStartRow) {

                    $sheet->mergeCells("A{$dayStartRow}:A{$highestRow}");
                }


            },
        ];
    }
}
