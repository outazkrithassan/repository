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

class Vols_somaine_export implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents, WithCustomStartCell
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $annee;
    protected $min_date_vol = null;
    protected $max_date_vol = null;

    public function collection()
    {
        $sql = "WITH ALL_VOLS AS (
            SELECT
                DATE_FORMAT(va.date_vol, '%Y-%u') AS week_year,
                COUNT(*) AS flight_count,
                DATE_FORMAT(MAX(va.date_vol), '%d %M %Y') AS max_date_vol,
                DATE_FORMAT(MIN(va.date_vol), '%d') AS min_date_vol
            FROM
                vol_arrives va
            GROUP BY
                DATE_FORMAT(va.date_vol, '%Y-%u')
            ORDER BY
                flight_count DESC,
                week_year DESC
            LIMIT 1
        )
        SELECT
            COALESCE(va.numero, '') AS numero_arrivee,
            COALESCE(cc_arrivee.name, '-') AS arrivee,
            vc_arrive.nom AS arrivee_company,
            COALESCE(DATE_FORMAT(va.heure_arrive, '%Hh%i'), '-') AS heure_arrive,
            COALESCE(vd.numero, '') AS numero_depart,
            COALESCE(cc_depart.name, '-') AS depart,
            vc_depart.nom AS depart_company,
            COALESCE(DATE_FORMAT(vd.heure_depart, '%Hh%i'), '-') AS heure_depart,

            DATE_FORMAT(COALESCE(vd.date_vol, va.date_vol), '%d %M %Y') AS date_vol,
            a.equipement,
            a.capacite,
            sa.annee,
            av.max_date_vol,
            av.min_date_vol

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
        LEFT JOIN
            saisons sa ON va.saison_id = sa.id OR vd.saison_id = sa.id
        JOIN
            ALL_VOLS av ON DATE_FORMAT(va.date_vol, '%Y-%u') = av.week_year
        WHERE
            DATE_FORMAT(va.date_vol, '%Y-%u') = (SELECT week_year FROM ALL_VOLS)
        UNION
        SELECT
            COALESCE(va.numero, '') AS numero_arrivee,
            COALESCE(cc_arrivee.name, '-') AS arrivee,
            vc_arrive.nom AS arrivee_company,
            COALESCE(DATE_FORMAT(va.heure_arrive, '%Hh%i'), '-') AS heure_arrive,
            COALESCE(vd.numero, '') AS numero_depart,
            COALESCE(cc_depart.name, '-') AS depart,
            vc_depart.nom AS depart_company,
            COALESCE(DATE_FORMAT(vd.heure_depart, '%Hh%i'), '-') AS heure_depart,
            DATE_FORMAT(COALESCE(vd.date_vol, va.date_vol), '%d %M %Y') AS date_vol,
            a.equipement,
            a.capacite,
            sa.annee,
            av.max_date_vol,
            av.min_date_vol
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
        LEFT JOIN
            saisons sa ON va.saison_id = sa.id OR vd.saison_id = sa.id
        JOIN
            ALL_VOLS av ON DATE_FORMAT(vd.date_vol, '%Y-%u') = av.week_year
        WHERE
            DATE_FORMAT(vd.date_vol, '%Y-%u') = (SELECT week_year FROM ALL_VOLS)
        ORDER BY
            date_vol;

        ";

    // Log the SQL query for debugging purposes
    Log::info('SQL Query:', ['query' => $sql]);

    // Execute the query and get the results
    $results = DB::select($sql);

    $retour = [];
    $min_date_vol = null;
    $max_date_vol = null;

    if (!empty($results)) {
        $this->annee = $results[0]->annee;
    } else {
        $this->annee = 'N/A'; // Default value if no results
    }
    if (is_null($min_date_vol)) {
        $this->min_date_vol = $results[0]->min_date_vol;
    }
    if (is_null($max_date_vol)) {
        $this->max_date_vol = $results[0]->max_date_vol;
    }


    foreach ($results as $key => $val) {
        $separator = $val->numero_arrivee != "" && $val->numero_depart != "" ? "/" : "";
        $company = $val->arrivee_company ? $val->arrivee_company : $val->depart_company;
        $numero = $company . ' ' . $val->numero_arrivee . $separator . $val->numero_depart;


        $retour[] = [
            'date_vol' => $val->date_vol,
            'numero' => $numero,
            'type_app' => $val->equipement,
            'capacite' => $val->capacite,
            'assistant' => 'todo',
            'arrivee' => $val->arrivee,
            'heure_arrive' => $val->heure_arrive,
            'depart' => $val->depart,
            'heure_depart' => $val->heure_depart,
        ];
    }

    $retour = $this->removeDuplicateArrays($retour);

    // Convert the results to a collection
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
                $sheet->setCellValue('A8', 'Programme prévisionnel été ' . $this->annee);
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
                $sheet->getColumnDimension('C')->setWidth('10');
                $sheet->getColumnDimension('C')->setAutoSize(false);
                $sheet->getColumnDimension('D')->setWidth('10');
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
