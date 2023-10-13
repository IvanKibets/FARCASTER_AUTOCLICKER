<?php

namespace App\Http\Controllers;
use App\Models\ProductType;
use Illuminate\Http\Request;

class ProductTypeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function insert(Request $r)
    {
        $this->validate($r,[
            'label' => 'required'
        ]);

        return response()->json(['message'=>'success']);
    }

    //
}
