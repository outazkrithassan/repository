<?php

namespace App\Http\Controllers;
use App\Models\VolDepart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DepartController extends Controller
{
    //
    public function index()
    {
        $saisons = DB::table('saisons')->get();
        $companies = DB::table('companies')->get();
        $avions = DB::table('avions')->get();
        return view('pages.Vols.Vol_depart', compact('saisons','companies','avions'));
    }


    public function create()
    {
        //
        $saisons = DB::table('saisons')->get();
        $companies = DB::table('companies')->get();
        $avions = DB::table('avions')->get();
        $vol_depart = [];
        return view("pages.base_donne.modal.create_vol_depart", compact('vol_depart','saisons','companies','avions'));
    }
    public function datatable()
    {
        $departs = DB::select("SELECT
            vol_departs.id,
            vol_departs.date_vol,
            CONCAT(companies.nom, ' ', vol_departs.numero) AS numero_vol,
            avions.equipement,
            city_codes.name AS destination,
            CONCAT(SUBSTRING(vol_departs.heure_depart, 1, 2), 'h', SUBSTRING(vol_departs.heure_depart, 3, 2)) AS heure_depart
        FROM
            vol_departs

        JOIN
            avions ON vol_departs.avion_id = avions.id
        JOIN
            companies ON vol_departs.companie_id = companies.id
        JOIN
            city_codes ON vol_departs.destination = city_codes.code
        WHERE
            vol_departs.saison_id = 1

        ");
        $dataTable = DataTables::of($departs)
            ->addColumn('action', function ($depart) {
                $edit = route('depart.edit', ['id' => $depart->id]);
                $delete = route('depart.destroy', ['id' => $depart->id]);
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
    public function edit($id)
    {
        //
        $vol_depart = VolDepart::findOrFail($id);
        $saisons = DB::table('saisons')->get();
        $companies = DB::table('companies')->get();
        $avions = DB::table('avions')->get();
        return view("pages.base_donne.modal.create_vol_depart", compact('vol_depart','saisons','companies','avions'));
    }
    public function store(Request $request)
    {
        //
        $request->validate([

            "numero_depart" => "required",
            "depart" => "required",
            "Heure_depart" => "required",
            "distance" => "required",
            "date_vol" => "required",
            "compagnie" => "required",
            "avion" => "required",
            "saison" => "required",
        ]);

        VolDepart::create([

            "numero" => $request->numero_depart,
            "destination" => $request->depart,
            "heure_depart" => $request->Heure_depart,
            "distance" => $request->distance,
            "date_vol" => $request->date_vol,
            "companie_id" => $request->compagnie,
            "avion_id" => $request->avion,
            "saison_id" => $request->saison
        ]);

        return response()->json(["ok" => true, "type" => "success", "message"  => "Le vol de depart crée avec succée"]);
    }
    public function update(Request $request, $id)
    {
        //
        $vol_depart = VolDepart::findOrFail($id);

        $request->validate([
            "saison" => "required",
            "numero_depart" => "required",
            "depart" => "required",
            "Heure_depart" => "required",
            "distance" => "required",
            "date_vol" => "required",
            "compagnie" => "required",
            "avion" => "required",
        ]);
        $vol_depart->numero = $request->numero_depart;
        $vol_depart->destination = $request->depart;
        $vol_depart->heure_depart = $request->Heure_depart;
        $vol_depart->distance= $request->distance;
        $vol_depart->date_vol = $request->date_vol;
        $vol_depart->companie_id = $request->compagnie;
        $vol_depart->avion_id = $request->avion;
        $vol_depart->saison_id = $request->saison;


        if ($vol_depart->update())
            return response()->json(["ok" => true, "type" => "success", "message"  => "Les vol de depart modifier avec succée"]);
    }
    public function destroy($id)
    {
        //
        $vol_depart = VolDepart::findOrFail($id);
        if ($vol_depart->delete()) {
            return response()->json(["ok" => true, "type" => "success", "message"  => "Le vol de depart supprimer avec succée"]);
        }
    }
}
