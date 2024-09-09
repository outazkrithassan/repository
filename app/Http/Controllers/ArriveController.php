<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Models\VolArrive;
class ArriveController extends Controller
{
    //
    public function index()
    {
        $saisons = DB::table('saisons')->get();
        $companies = DB::table('companies')->get();
        $avions = DB::table('avions')->get();

        return view('pages.Vols.Vol_arrivee', compact('saisons','companies','avions'));
    }
    public function datatable()
    {
        $arrivee = DB::select("SELECT
                vol_arrives.id,
                vol_arrives.date_vol,
                CONCAT(companies.nom, ' ', vol_arrives.numero) AS numero_vol, -- Concatenated 'N° vol'
                avions.equipement, -- 'Type avion'
                city_codes.name AS depart, -- 'depart' (departure city name)
                CONCAT(SUBSTRING(vol_arrives.heure_arrive, 1, 2), 'h', SUBSTRING(vol_arrives.heure_arrive, 3, 2)) AS heure_arrive -- Formatted as hh:mm
            FROM
                vol_arrives
            JOIN
                avions ON vol_arrives.avion_id = avions.id
            JOIN
                companies ON vol_arrives.companie_id = companies.id
            JOIN
                city_codes ON vol_arrives.depart = city_codes.code
            WHERE
            vol_arrives.saison_id = 1
        ");
        $dataTable = DataTables::of($arrivee)
            ->addColumn('action', function ($arrive) {
                $edit = route('arrive.edit', ['id' => $arrive->id]);
                $delete = route('arrive.destroy', ['id' => $arrive->id]);
                return '
                    <ul class="list-inline hstack gap-1 justify-content-center mb-0">
                      <li class="list-inline-item edit" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Edit">
                           <a  route="' . $edit . '"  type="button" class="dropdown-item edit">
                                <i class="mdi mdi-pencil fs-16 text-success me-1"></i>
                            </a>
                        </li>
                        <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Remove">
                            <div class="btn-group" role="group">
                                    <a id="delete-dropdown" type="button" class="text-danger d-inline-block remove-item-btn dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="ri-delete-bin-5-fill fs-16"></i>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="delete-dropdown">
                                        <a type="button" class="dropdown-item delete" table="vol_frere" route="' . $delete . '" >Confirmer</a>
                                    </div>
                            </div>
                        </li>

                    </ul>
                ';
            })
            ->rawColumns(['action']);

        return $dataTable->make(true);
    }
    public function create()
    {
        //
        $saisons = DB::table('saisons')->get();
        $companies = DB::table('companies')->get();
        $avions = DB::table('avions')->get();
        $vol_arrive = [];
        return view("pages.base_donne.modal.create_vol_arrivee", compact('vol_arrive','saisons','companies','avions'));
    }
public function edit($id)
    {
        //
        $vol_arrive = VolArrive::findOrFail($id);
        $saisons = DB::table('saisons')->get();
        $companies = DB::table('companies')->get();
        $avions = DB::table('avions')->get();
        return view("pages.base_donne.modal.create_vol_arrivee", compact('vol_arrive','saisons','companies','avions'));
    }
    public function store(Request $request)
    {
        //
        $request->validate([

            "numero_arrivee" => "required",
            "arrive" => "required",
            "heure_arrive" => "required",
            "distance" => "required",
            "date_vol" => "required",
            "compagnie" => "required",
            "avion" => "required",
            "saison" => "required",
        ]);

        VolArrive::create([

            "numero" => $request->numero_arrivee,
            "depart" => $request->arrive,
            "heure_arrive" => $request->heure_arrive,
            "distance" => $request->distance,
            "date_vol" => $request->date_vol,
            "companie_id" => $request->compagnie,
            "avion_id" => $request->avion,
            "saison_id" => $request->saison
        ]);

        return response()->json(["ok" => true, "type" => "success", "message"  => "Le vol de arrivee crée avec succée"]);
    }
    public function update(Request $request, $id)
    {
        //
        $vol_arrive = VolArrive::findOrFail($id);

        $request->validate([
            "saison" => "required",
            "numero_arrivee" => "required",
            "arrive" => "required",
            "heure_arrive" => "required",
            "distance" => "required",
            "date_vol" => "required",
            "compagnie" => "required",
            "avion" => "required",
        ]);
        $vol_arrive->numero = $request->numero_arrivee;
        $vol_arrive->depart = $request->arrive;
        $vol_arrive->heure_arrive = $request->heure_arrive;
        $vol_arrive->distance= $request->distance;
        $vol_arrive->date_vol = $request->date_vol;
        $vol_arrive->companie_id = $request->compagnie;
        $vol_arrive->avion_id = $request->avion;
        $vol_arrive->saison_id = $request->saison;


        if ($vol_arrive->update())
            return response()->json(["ok" => true, "type" => "success", "message"  => "Les vol de arrivee modifier avec succée"]);
    }
    public function destroy($id)
    {
        //
        $vol_arrive = VolArrive::findOrFail($id);
        if ($vol_arrive->delete()) {
            return response()->json(["ok" => true, "type" => "success", "message"  => "Le vol de arrivee supprimer avec succée"]);
        }
    }
}
