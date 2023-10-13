<?php

namespace App\Http\Controllers;
use App\Models\PulsaOperatorPrefix;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Models\ProductOperator;
use Illuminate\Http\Request;
use App\Jobs\RequestKoperasi;

class PulsaController extends Controller
{
    public $status = "success";

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    public function transaction(Request $r)
    {
        log_activity('Transaction Pulsa');

        $this->validate($r,[
            'no'=>'required',
            'metode_pembayaran'=>'required',
            'product_code'=>'required'
        ]);
        
        $product = Product::where('kode_produk',$r->product_code)->first();

        if(!$product) return response()->json(['message'=>'failed','data'=>'Product Not Found.']);
 
        $transaction = new Transaction();
        $transaction->product_id = $product->id;
        $transaction->price = $product->harga_jual;
        $transaction->user_id = \Auth::user()->id;
        $transaction->no_telepon = $r->no;
        $transaction->metode_pembayaran = $r->metode_pembayaran;
        $transaction->save();
        $transaction->transaction_id = $transaction->id.date('ymdhi').\Auth::user()->id.str_pad((Transaction::count()+1),4, '0', STR_PAD_LEFT);
        $transaction->save();

        $item = new TransactionItem();
        $item->transaction_id = $transaction->id;
        $item->price = $product->harga_jual;
        $item->product_id = $product->id;
        $item->description = $product->keterangan_detail .' - '. $r->no;
        $item->save();

        //  Production
        // $response = digiflazz([
        //     'id'=>$transaction->id,
        //     'type'=>'production', // Demo  or Production
        //     'product'=>$product->kode_produk,
        //     'no'=>$r->no,
        //     'action'=>'topup',
        //     'ref_id'=>$transaction->transaction_id
        // ]);

        // $transaction->api_response_before = $response;
        // $transaction->save();

        // $txt = json_decode($response);

        // $response = json_decode($response);
        // if($response->data->rc=='00'){ // sukses
        //     $transaction->status = 1;
        //     $transaction->save();
        // }
        
         // Integration to Koperasi
        // $koperasi = \Auth::user()->koperasi;
        $params = 
            [
                'url'=>"/api/transaction-store-pulsa",
                'transaction_type'=>1, // pulsa
                'transaction_id'=>$transaction->transaction_id,
                'price'=> $product->harga_jual,
                'metode_pembayaran'=>$r->metode_pembayaran,
                'reference_no'=>$r->no,
                'product_code'=>$r->product_code,
                'product'=>$product->keterangan_detail .' - '. $r->no,
                'date'=>date('Y-m-d'),
                'status'=>$transaction->status,
                'qty'=>1,
                'total'=>$product->harga_jual
            ];

        $this->dispatch(new RequestKoperasi(\Auth::user(),$transaction,$params));

        // // Integration to Koperasi
        // $koperasi = \Auth::user()->koperasi;
        // $sinkron = sinkronKoperasi(
        //     [
        //         'url'=>"{$koperasi->url}/api/transaction-store-pulsa",
        //         'token'=> $koperasi->token,
        //         'transaction_type'=>1, // pulsa
        //         'transaction_id'=>$transaction->transaction_id,
        //         'no_anggota'=>\Auth::user()->no_anggota,
        //         'price'=> $product->harga_jual,
        //         'metode_pembayaran'=>$r->metode_pembayaran,
        //         'reference_no'=>$r->no,
        //         'product_code'=>$r->product_code,
        //         'product'=>$product->keterangan_detail .' - '. $r->no,
        //         'date'=>date('Y-m-d'),
        //         'status'=>$transaction->status,
        //         'qty'=>1,
        //         'total'=>$product->harga_jual
        //     ]
        // );
        
        // if($sinkron->status()==200){
        //     $transaction->api_response_after = $sinkron->body();
        //     $response = json_decode($sinkron->body());
        //     $transaction->status = $response->status;
        //     $transaction->keterangan_gagal = $response->message;
        //     $transaction->save();
        // }
        
        return response()->json(['status'=>$this->status,'message'=>'success']);
    }

    public function cekPrefix(Request $r)
    {   
        $data = [];
        $no = nomor_handphone($r->no);
        $prefix = substr($no, 0, 4);

        $operator_pulsa   = ProductOperator::where('prefix', 'LIKE', "%{$prefix}%")->where('product_type_id',9)->first();
        $operator_paketdata   = ProductOperator::where('prefix', 'LIKE', "%{$prefix}%")->where('product_type_id',4)->first();
        $data['prefix'] = $prefix;
        $data['data_pulsa'] = [];
        $data['data_paketdata'] = [];
        $data['status'] = 200;

        if(strlen($prefix) >= 4){
            if($operator_pulsa){
                $data['data_pulsa'] = Product::select('kode_produk','harga_jual','keterangan')->where(['product_operator_id'=>$operator_pulsa->id])->get();
                $temp= [];
                foreach($data['data_pulsa'] as $k => $item){
                    $temp[$k]['kode_produk'] = $item->kode_produk;
                    $temp[$k]['harga_jual'] = format_idr($item->harga_jual);
                    $temp[$k]['keterangan'] = $item->keterangan;
                }
                $data['data_pulsa'] = $temp;
            }
            if($operator_paketdata){
                $data['data_paketdata'] = Product::select('kode_produk','harga_jual','keterangan')->where(['product_operator_id'=>$operator_paketdata->id])->get();
                $temp = [];
                foreach($data['data_paketdata'] as $k => $item){
                    $temp[$k]['kode_produk'] = $item->kode_produk;
                    $temp[$k]['harga_jual'] = format_idr($item->harga_jual);
                    $temp[$k]['keterangan'] = $item->keterangan;
                }
                $data['data_paketdata'] = $temp;
            }
        }
        
        return response()->json(['message'=>'success','data'=>$data]);
    }

    public function topup(Request $r)
    {
        $this->validate($r,[
            'no_handphone'=>'required',
            'product_code'=>'required'
        ]);

        $product = Product::where('kode_produk',$r->product_code)->first();
        
        if(!$product) return response()->json(['code'=>404,'message'=>'product not found']);
        
        $data = new Transaction();
        $data->product_id = $product->id;
        $data->price = $product->harga_jual;
        $data->status = 0;
        $data->save(); 

        $data->transaction_id = $data->id . date('Ymdhis');
        $iak = iak();
        
        $response  = $iak->topUp([
            'customerId'=>$r->no_handphone,
            'refId'=>$data->transaction_id,
            'productCode'=>$r->product_code
        ]);

        $data->api_response_before = $response;
        $data->save();

        return response()->json(['code'=>200,'data'=>$response]);
    }

    public function cekTransaksi(Request $r)
    {
        $this->validate($r,[
            'ref_id'=>'required',
        ]);

        $transaction = Transaction::where('transaction_id',$r->ref_id)->first();

        $response = iak()->checkStatus(['refId'=>$r->ref_id]);

        return response()->json(['code'=>200,'data'=>$response]);
    }
}
