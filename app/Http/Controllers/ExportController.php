<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    //
    public function index()
    {
        //
        $saisons = DB::table('saisons')->get();
        return view('pages.base_donne.export', compact('saisons'));
    }
}
