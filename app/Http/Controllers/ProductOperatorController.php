<?php

namespace App\Http\Controllers;
use App\Models\ProductOperator;
use Illuminate\Http\Request;

class ProductOperatorController extends Controller
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
            'product_type_id'=>'required',
            'name' => 'required'
        ]);

        $data = ProductOperator::where(['product_type_id'=>$r->product_type_id,'name'=>$r->name])->first();
        if(!$data){
            $data = new ProductOperator();
            $data->product_type_id = $r->product_type_id;
            $data->name = $r->name;
            $data->save();
        }
        return response()->json(['message'=>'success','data'=>$data]);
    }

    //
}
