<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VolFrere extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_arrivee',
        'numero_depart'
    ];
}
