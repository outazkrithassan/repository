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

class VolsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents, WithCustomStartCell
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $annee;
    public function collection()
    {
        set_time_limit(300);

        $sql = "WITH date_time_range AS (
            SELECT
                numero,
                jour_semaine,
                heure,
                MIN(date_vol) AS min_date,
                MAX(date_vol) AS max_date
            FROM (
                SELECT
                    vd.numero,
                    CASE
                        DAYOFWEEK(vd.date_vol)
                        WHEN 1 THEN 'dimanche'
                        WHEN 2 THEN 'lundi'
                        WHEN 3 THEN 'mardi'
                        WHEN 4 THEN 'mercredi'
                        WHEN 5 THEN 'jeudi'
                        WHEN 6 THEN 'vendredi'
                        WHEN 7 THEN 'samedi'
                    END AS jour_semaine,
                    CONCAT(SUBSTR(vd.heure_depart, 1, 2), 'h', SUBSTR(vd.heure_depart, 3, 2)) AS heure,
                    vd.date_vol
                FROM vol_departs vd
                UNION ALL
                SELECT
                    va.numero,
                    CASE
                        DAYOFWEEK(va.date_vol)
                        WHEN 1 THEN 'dimanche'
                        WHEN 2 THEN 'lundi'
                        WHEN 3 THEN 'mardi'
                        WHEN 4 THEN 'mercredi'
                        WHEN 5 THEN 'jeudi'
                        WHEN 6 THEN 'vendredi'
                        WHEN 7 THEN 'samedi'
                    END AS jour_semaine,
                    CONCAT(SUBSTR(va.heure_arrive, 1, 2), 'h', SUBSTR(va.heure_arrive, 3, 2)) AS heure,
                    va.date_vol
                FROM vol_arrives va
            ) AS all_vols
            GROUP BY numero, jour_semaine, heure
        )
        SELECT
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
            CASE
                DAYOFWEEK(vd.date_vol)
                WHEN 1 THEN 'dimanche'
                WHEN 2 THEN 'lundi'
                WHEN 3 THEN 'mardi'
                WHEN 4 THEN 'mercredi'
                WHEN 5 THEN 'jeudi'
                WHEN 6 THEN 'vendredi'
                WHEN 7 THEN 'samedi'
            END AS jour_semaine,
            a.equipement,
            a.capacite,
            sa.annee,
            dtr.min_date,
            dtr.max_date,
            CASE
                DAYOFWEEK(vd.date_vol)
                WHEN 2 THEN 1
                WHEN 3 THEN 2
                WHEN 4 THEN 3
                WHEN 5 THEN 4
                WHEN 6 THEN 5
                WHEN 7 THEN 6
                WHEN 1 THEN 7
            END AS jour_order
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
        LEFT JOIN
            saisons sa ON va.saison_id = sa.id OR vd.saison_id = sa.id
        LEFT JOIN
            date_time_range dtr ON vd.numero = dtr.numero AND
                                  CASE
                                      DAYOFWEEK(vd.date_vol)
                                      WHEN 1 THEN 'dimanche'
                                      WHEN 2 THEN 'lundi'
                                      WHEN 3 THEN 'mardi'
                                      WHEN 4 THEN 'mercredi'
                                      WHEN 5 THEN 'jeudi'
                                      WHEN 6 THEN 'vendredi'
                                      WHEN 7 THEN 'samedi'
                                  END = dtr.jour_semaine AND
                                  CONCAT(SUBSTR(vd.heure_depart, 1, 2), 'h', SUBSTR(vd.heure_depart, 3, 2)) = dtr.heure
        UNION ALL
        SELECT
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
            CASE
                DAYOFWEEK(va.date_vol)
                WHEN 1 THEN 'dimanche'
                WHEN 2 THEN 'lundi'
                WHEN 3 THEN 'mardi'
                WHEN 4 THEN 'mercredi'
                WHEN 5 THEN 'jeudi'
                WHEN 6 THEN 'vendredi'
                WHEN 7 THEN 'samedi'
            END AS jour_semaine,
            a.equipement,
            a.capacite,
            sa.annee,
            dtr.min_date,
            dtr.max_date,
            CASE
                DAYOFWEEK(va.date_vol)
                WHEN 2 THEN 1
                WHEN 3 THEN 2
                WHEN 4 THEN 3
                WHEN 5 THEN 4
                WHEN 6 THEN 5
                WHEN 7 THEN 6
                WHEN 1 THEN 7
            END AS jour_order
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
        LEFT JOIN
            saisons sa ON va.saison_id = sa.id OR vd.saison_id = sa.id
        LEFT JOIN
            date_time_range dtr ON va.numero = dtr.numero AND
                                  CASE
                                      DAYOFWEEK(va.date_vol)
                                      WHEN 1 THEN 'dimanche'
                                      WHEN 2 THEN 'lundi'
                                      WHEN 3 THEN 'mardi'
                                      WHEN 4 THEN 'mercredi'
                                      WHEN 5 THEN 'jeudi'
                                      WHEN 6 THEN 'vendredi'
                                      WHEN 7 THEN 'samedi'
                                  END = dtr.jour_semaine AND
                                  CONCAT(SUBSTR(va.heure_arrive, 1, 2), 'h', SUBSTR(va.heure_arrive, 3, 2)) = dtr.heure
        -- WHERE
        --     va.numero NOT IN (SELECT numero_arrivee FROM vol_freres)
        --     AND vd.numero NOT IN (SELECT numero_depart FROM vol_freres)
        ORDER BY
            jour_order



        ";
        // Log the SQL query for debugging purposes
        Log::info('SQL Query:', ['query' => $sql]);

        // Execute the query and get the results
        $results = DB::select($sql);
        // echo "<pre>";
        // print_r($results);
        // echo "</pre>";
        // die;
        $retour = [] ;
        if (!empty($results)) {
            $this->annee = $results[0]->annee;
        } else {
            $this->annee = 'N/A'; // Default value if no results
        }
        foreach( $results as $key => $val ) {
            $separator = $val->numero_arrivee != "" && $val->numero_depart != "" ? "/" : "" ;
            $company = $val->arrivee_company ? $val->arrivee_company : $val->depart_company ;
            $numero = $company .' '. $val->numero_arrivee . $separator .$val->numero_depart ;


            $retour[] = [
                'jour_semaine' => $val->jour_semaine,
                'numero' => $numero  ,
                'type_app' => $val->equipement ,
                'capacite' => $val->capacite ,
                'assistant' => 'todo' ,
                'arrivee' => $val->arrivee ,
                'heure_arrive' => $val->heure_arrive ,
                'depart' => $val->depart ,
                'heure_depart' => $val->heure_depart,
                'min' => $val->min_date,
                'max' => $val->max_date

            ] ;

        }

        $retour = $this->removeDuplicateArrays($retour) ;

        // Convert the results to a collection and return
        return collect($retour);
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
                $sheet->setCellValue('A6', 'Programme prévisionnel été ' . $this->annee);
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
                $sheet->getColumnDimension('C')->setWidth('10');
                $sheet->getColumnDimension('C')->setAutoSize(false);
                $sheet->getColumnDimension('D')->setWidth('10');
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
