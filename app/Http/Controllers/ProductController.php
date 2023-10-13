<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Jobs\SinkronKoperasi;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }
    
    public function scan(Request $r)
    {
        $this->validate($r,[
            'no'=>'required'
        ]);

        $data = [
            'id'=>'',
            'nama' => '',
            'qty'=>'',
            'expired_date'=>''
        ];
        
        $product = Product::where(['kode_produksi'=>$r->no,'koperasi_id'=>\Auth::user()->koperasi_id])->first();
        if($product){
            $data['id'] = $product->id;
            $data['nama'] = $product->keterangan;
            $data['qty'] = $product->qty;
            $data['expired_date'] = $product->expired_date;
            $data['harga'] = $product->harga;
            $data['harga_jual'] = $product->harga_jual;
        }
       
        return response()->json(['status'=>'success','data'=>$data]);
    }

    public function store(Request $r)
    {
        $this->validate($r,[
            'no'=>'required',
            'nama'=>'required',
            'qty'=>'required'
        ]);

        $product = Product::where(['kode_produksi'=>$r->no,'koperasi_id'=>\Auth::user()->koperasi_id])->first();
        if(!$product) $product = new Product();
        
        $product->kode_produksi = $r->no;
        $product->koperasi_id = \Auth::user()->koperasi_id;
        $product->qty = $r->qty;
        $product->keterangan = $r->nama;
        $product->harga = $r->harga?$r->harga:0;
        $product->harga_jual = $r->harga_jual?$r->harga_jual:0;
        $product->save();

        $params = 
            [
                'url'=>"/api/product/update",
                'data'=>[
                    'kode_produksi'=>$product->kode_produksi,
                    'qty'=>$product->qty,
                    'keterangan'=>$product->keterangan,
                    'harga'=>$product->harga,
                    'harga_jual'=>$product->harga_jual
                ]
            ];

        $this->dispatch(new SinkronKoperasi(\Auth::user(),$params));

        return response()->json(['status'=>'success']);
    }
}
