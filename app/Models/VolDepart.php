<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VolDepart extends Model
{
    use HasFactory;
    protected $fillable = [
        'numero',
        'destination',
        'heure_depart',
        'distance',
        'date_vol',
        'companie_id',
        'avion_id',
        'saison_id'
    ];
}
