<?php

namespace App\Http\Controllers;

use App\Models\VolFrere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class VolFrereController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view("pages.base_donne.vol_frere");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $vol_frere = [];
        return view("pages.base_donne.modal.create_vol_frere", compact("vol_frere"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            "numero_arrivee" => "required",
            "numero_depart" => "required",
        ]);

        VolFrere::create([
            "numero_arrivee" => $request->numero_arrivee,
            "numero_depart" => $request->numero_depart
        ]);

        return response()->json(["ok" => true, "type" => "success", "message"  => "Les numéro crée avec succée"]);
    }

    /**
     * Display the specified resource.
     */
    public function show(VolFrere $volFrere)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
        $vol_frere = VolFrere::findOrFail($id);

        return view("pages.base_donne.modal.create_vol_frere", compact("vol_frere"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        $vol_frere = VolFrere::findOrFail($id);

        $request->validate([
            "numero_arrivee" => "required",
            "numero_depart" => "required",
        ]);

        $vol_frere->numero_arrivee = $request->numero_arrivee;
        $vol_frere->numero_depart = $request->numero_depart;

        if ($vol_frere->update())
            return response()->json(["ok" => true, "type" => "success", "message"  => "Les numéro modifier avec succée"]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $vol_frere = VolFrere::findOrFail($id);
        if ($vol_frere->delete()) {
            return response()->json(["ok" => true, "type" => "success", "message"  => "Le numéro supprimer avec succée"]);
        }
    }


    public function datatable()
    {
        $vol_freres = DB::select("SELECT 
                id as id ,
                numero_arrivee as numero_arrivee ,
                numero_depart as numero_depart 
                from vol_freres 
        ");

        $dataTable = DataTables::of($vol_freres)
            ->addColumn('action', function ($frere) {
                $edit = route('frere.edit', ['id' => $frere->id]);
                $delete = route('frere.destroy', ['id' => $frere->id]);
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
}
