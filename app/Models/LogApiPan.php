<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogApiPan extends Model
{
   protected $connection = 'ajrius';
   
   protected $table = 'log_api_pan';
}
