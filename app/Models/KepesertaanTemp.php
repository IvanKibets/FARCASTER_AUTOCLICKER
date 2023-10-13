<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KepesertaanTemp extends Model
{
    use HasFactory;

    protected $table = 'kepesertaan_temp';
    
    protected $connection = 'ajrius';
}
