<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vol extends Model
{
    use HasFactory;



    protected $fillable = [
        'numero',
        'depart',
        'destination',
        'heure_depart',
        'heure_arrive',
        'distance',
        'date_vol',
        'companie_id',
        'avion_id',
        'saison_id'
    ];
}
