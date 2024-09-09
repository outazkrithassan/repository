<?php

namespace App\Http\Controllers;

use App\Imports\ImportVols;
use App\Models\Saison;
use App\Models\Vol;
use App\Models\VolArrive;
use App\Models\VolDepart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;


class ImportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view("pages.base_donne.import");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $saison = [];
        return view("pages.base_donne.modal.create_import", compact('saison'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'annee' => 'required|size:4|unique:saisons,annee',
            'data' => 'required|mimes:xlsx'
        ]);

        $saison_id = Saison::create([
            "annee" => $request->annee
        ])->id;

        $this->import($request, $saison_id);
        return response()->json(['ok' => true, "type" => "success",  "message" =>  'Les données  importer avec succés !']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $saison = [];
        return view("pages.import.modal.create", compact('saison'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $saison = Saison::find($id);

        if ($saison) {
            // Fetch and delete all 'VolArrive' records for the specified 'saison_id'
            $volsArrive = VolArrive::where('saison_id', $id)->get();
            foreach ($volsArrive as $volArrive) {
                $volArrive->delete();
            }

            // Fetch and delete all 'VolDepart' records for the specified 'saison_id'
            $volsDepart = VolDepart::where('saison_id', $id)->get();
            foreach ($volsDepart as $volDepart) {
                $volDepart->delete();
            }

            // Finally, delete the 'Saison'
            $saison->delete();
        }

        return response()->json(['ok' => true, "type" => "success",  "message" =>  'Les données supprimer avec succés !']);
    }



    public function datatable()
    {
        $saisons = DB::select("SELECT
                id as id ,
                annee as annee
                from saisons
        ");

        $dataTable = DataTables::of($saisons)
            ->addColumn('action', function ($saison) {
                $edit = route('import.edit', ['id' => $saison->id]);
                $delete = route('import.destroy', ['id' => $saison->id]);
                return '
                    <ul class="list-inline hstack gap-1 justify-content-center mb-0">
                        <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Remove">

                            <div class="btn-group" role="group">
                                    <a id="delete-dropdown" type="button" class="text-danger d-inline-block remove-item-btn dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="ri-delete-bin-5-fill fs-16"></i>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="delete-dropdown">
                                        <a type="button" class="dropdown-item delete" table="saison" route="' . $delete . '" >Confirmer</a>
                                    </div>
                            </div>
                        </li>
                    </ul>
                ';
            })
            ->rawColumns(['action']);

        return $dataTable->make(true);
    }


    public function import(Request $request, $saison_id)
    {

        Excel::import(new ImportVols($saison_id), $request->file('data'));
    }
}
