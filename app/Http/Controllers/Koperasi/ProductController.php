<?php

namespace App\Http\Controllers\Koperasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Koperasi;
use Illuminate\Support\Facades\Hash;

class ProductController extends Controller
{
    public $status='success';
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth:api', ['except' => []]);
    }

    public function sync(Request $r)
    {
        $this->validate($r,[
            'token'=>'required',
            'data'=>'required',
        ]);
        $koperasi = Koperasi::where(['token'=>$r->token])->first();
        if($koperasi){
            $product = Product::where('kode_produksi',$r->data['kode_produksi'])->first();
            if(!$product) {
                $product = new Product();
                $product->kode_produksi = $r->data['kode_produksi'];
            }
            $product->keterangan = $r->data['keterangan'];
            $product->qty = $r->data['qty'];
            $product->harga_jual = $r->data['harga_jual'];
            $product->harga = $r->data['harga'];
            $product->koperasi_id = $koperasi->id;
            $product->save();
        }else $this->status = 'failed';
        
        return response()->json(['status'=>$this->status,'data'=>$r->all()], 200);
    }
}