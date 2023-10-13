<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Produk;

class Polis extends Model
{
    use HasFactory;

    protected $table = 'polis';
    
    protected $connection = 'ajrius';

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}
