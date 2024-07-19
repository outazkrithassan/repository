<?php

namespace App\Imports;

use App\Models\Avion;
use App\Models\Companie;
use App\Models\Saison;
use App\Models\Vol;
use App\Models\VolArrive;
use App\Models\VolDepart;
use DateInterval;
use DateTime;
use Maatwebsite\Excel\Concerns\ToModel;

use function PHPUnit\Framework\isNull;

class ImportVols implements ToModel
{


    protected $saison_id;

    public function __construct($saison_id)
    {
        $this->saison_id = $saison_id;
    }

    public function model(array $row)
    {
        ini_set('max_execution_time', 0);

        static $isFirstRow = true;

        if ($isFirstRow) {
            $isFirstRow = false;
            return null;
        }

        if ($this->verification($row)) {
            $avion_id = Avion::firstOrCreate([
                'equipement' => $row[10],
                'capacite' => $row[12],
            ])->id;

            $companie_id = Companie::firstOrCreate([
                'nom' => $row[0]=="TOM"?"BY":$row[0],
            ])->id;

            // Vol::create([
            //     'numero' => $row[1],
            //     'depart' => $row[2],
            //     'destination' => $row[3],
            //     'heure_depart' => $row[4],
            //     'heure_arrive' => $row[5],
            //     'distance' => $row[8],
            //     'date_vol' => $this->excelToPhpDate($row[13]),
            //     'companie_id' => $companie_id,
            //     'avion_id' => $avion_id,
            //     'saison_id' => $this->saison_id
            // ]);
            if ($row[2] == "AGA") {
                VolDepart::create([
                    'numero' => $row[1],
                    'destination' => $row[3],
                    'heure_depart' => $row[4],
                    'distance' => $row[8],
                    'date_vol' => $this->excelToPhpDate($row[13]),
                    'companie_id' => $companie_id,
                    'avion_id' => $avion_id,
                    'saison_id' => $this->saison_id
                ]);
            } else {
                VolArrive::create([
                    'numero' => $row[1],
                    'depart' => $row[2],
                    'heure_arrive' => $row[5],
                    'distance' => $row[8],
                    'date_vol' => $this->excelToPhpDate($row[13]),
                    'companie_id' => $companie_id,
                    'avion_id' => $avion_id,
                    'saison_id' => $this->saison_id
                ]);
            }
        }
    }


    private function verification($row)
    {
        $result = true;

        for ($i = 0; $i <= 13; $i++) {
            if ($row[$i] == "")
                $result = false;
        }

        return $result;
    }


    private function excelToPhpDate($excelDate)
    {
        $excelDate = intval($excelDate);
        return (new DateTime('1899-12-30'))->add(new DateInterval('P' . $excelDate . 'D'))->format('Y-m-d');
    }
}
