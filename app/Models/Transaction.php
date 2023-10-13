<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Product;
use App\Models\TransactionItem;

class Transaction extends Model
{
    public function items()
    {
        return $this->hasMany(TransactionItem::class,'transaction_id','id');
    }

    public function user()
    {
        return $this->hasOne(User::class,'id','user_id');
    }

    public function product()
    {
        return $this->hasOne(Product::class,'id','product_id');
    }
}
