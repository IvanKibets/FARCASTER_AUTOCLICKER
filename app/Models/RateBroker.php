<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RateBroker extends Model
{
   protected $connection = 'ajrius';
   
   protected $table = 'rate_broker';
}
