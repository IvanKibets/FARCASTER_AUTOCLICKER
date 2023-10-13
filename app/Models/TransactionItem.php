<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
class TransactionItem extends Model
{
    protected $table = 'transaction_item';

    public function product()
    {
        return $this->hasOne(Product::class,'id','product_id');
    }
}
