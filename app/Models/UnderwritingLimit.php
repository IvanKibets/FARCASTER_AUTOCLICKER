<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Produk;

class UnderwritingLimit extends Model
{
    use HasFactory;

    protected $table = 'underwriting_limit';
    
    protected $connection = 'ajrius';
}
