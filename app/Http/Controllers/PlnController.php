<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\TransactionTemp;
use App\Models\Product;
use App\Models\ProductOperator;
use Illuminate\Http\Request;

class PlnController extends Controller
{
    public $biaya_admin = 1500,$status="success",$message,$response=[];
    
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
        log_activity('Transaction Token');

        return response()->json(['message'=>'success']);
    }

    public function storeTagihan(Request $r)
    {
        $this->validate($r,[
            'no'=>'required',
            'metode_pembayaran'=>'required'
        ]);

        log_activity('Transaction Tagihan PLN');
        
        $temp = TransactionTemp::where('transaction_id',$r->ref_id)->first();
        $temp_data = json_decode($temp->data_json);

        $product = Product::where('kode_produk','plnpasca')->first();

        $transaction = new Transaction();
        $transaction->product_id = $product->id;
        $transaction->price = $temp_data->selling_price;
        $transaction->user_id = \Auth::user()->id;
        $transaction->no_telepon = $r->no;
        $transaction->metode_pembayaran = $r->metode_pembayaran;
        $transaction->save();
        $transaction->transaction_id = $transaction->id.date('ymdhi').\Auth::user()->id.str_pad((Transaction::count()+1),4, '0', STR_PAD_LEFT);
        $transaction->save();

        $item = new TransactionItem();
        $item->transaction_id = $transaction->id;
        $item->price = $temp_data->selling_price;
        $item->product_id = $product->id;
        $item->description = 'PLN Pascabayar No Pelanggan '. $r->no;
        $item->save();

        //  Production
        $response = digiflazz([
            'id'=>$transaction->id,
            'commands'=>'pay-pasca',
            'type'=>'production', // Demo  or Production
            'product'=>'pln',
            'no'=>$r->no,
            'action'=>'topup',
            'ref_id'=>$transaction->transaction_id
        ]);
        
        $transaction->api_response_before = $response;

        $response = json_decode($response);
        if($response->data->rc=='00'){ // sukses
            $transaction->status = 1;
        }else{
            $transaction->status = 2;
        }
        $transaction->save();

        return response()->json(['status'=>$this->status]);
    }

    public function storeToken(Request $r)
    {
        $this->validate($r,[
            'no'=>'required',
            'product_code'=>'required',
            'metode_pembayaran'=>'required'
        ]);

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
        $item->description = $product->keterangan .' No Pelanggan '. $r->no;
        $item->save();

        //  Production
        $response = digiflazz([
            'id'=>$transaction->id,
            'type'=>'production', // Demo  or Production
            'product'=>$product->kode_produk,
            'no'=>$r->no,
            'action'=>'topup',
            'ref_id'=>$transaction->transaction_id
        ]);
        
        $transaction->api_response_before = $response;
        $transaction->save();

        $response = json_decode($response);
        if($response->data->rc=='00'){ // sukses
            $transaction->status = 1;
            $transaction->save();
        }

        return response()->json(['message'=>'success']);
    }

    public function cekTagihan(Request $r)
    {
        $this->validate($r,[
            'no'=>'required'
        ]);

        $temp =  new TransactionTemp();
        $temp->description = 'pln';
        $temp->save();

        $temp->transaction_id = date('dmyhis').$temp->id;

        $response = digiflazz([
            'commands'=>'inq-pasca',
            'id'=>$temp->id,
            'type'=>'production', // Demo  or Production
            'product'=>'pln',
            'no'=>$r->no,
            'action'=>'cek-tagihan-token',
            'ref_id'=>$temp->transaction_id
        ]);

        // $response = '
        // {
        //     "data": {
        //       "ref_id": "some1d",
        //       "customer_no": "530000000001",
        //       "customer_name": "Nama Pelanggan Pertama",
        //       "buyer_sku_code": "i5",
        //       "admin": 2500,
        //       "message": "Transaksi Sukses",
        //       "status": "Sukses",
        //       "rc": "00",
        //       "buyer_last_saldo": 100000,
        //       "price": 10000,
        //       "selling_price": 11000,
        //       "desc": {
        //         "tarif": "R1",
        //         "daya": 1300,
        //         "lembar_tagihan": 1,
        //         "detail": [
        //           {
        //             "periode": "201901",
        //             "nilai_tagihan": "8000",
        //             "admin": "2500",
        //             "denda": "500"
        //           }
        //         ]
        //       }
        //     }
        //   }
        // ';

        $response = json_decode($response)->data;
        if($response->rc=='00'){
            $this->response['no'] = $response->customer_no;
            $this->response['name'] = $response->customer_name;
            $this->response['price'] = "Rp. ".format_idr($response->selling_price);
            $this->response['selling_price'] = $response->selling_price;
            $this->response['admin'] = "Rp. ".format_idr($response->admin);
            $this->response['daya'] = $response->desc->daya;
            $this->response['tarif'] = $response->desc->tarif;
            $this->response['detail'] = [];
            foreach($response->desc->detail as $k=>$detail){
                $this->response['detail'][$k]['periode'] = substr($detail->periode,0,4).'-'.substr($detail->periode,4,5);
                $this->response['detail'][$k]['tagihan'] = format_idr($detail->nilai_tagihan);
                $this->response['detail'][$k]['admin'] = format_idr($detail->admin);
                $this->response['detail'][$k]['denda'] = format_idr($detail->denda);
            }
        }else{
            $this->status = 'failed';
            $this->message = $response->message;
        }

        $temp->data_json = $this->response;
        $temp->save();

        return response()->json(['status'=>$this->status, 'message'=>$this->message, 'response'=>$this->response,'ref_id'=>$temp->transaction_id]);
    }

    public function cekTagihanToken(Request $r)
    {
        $this->validate($r,[
            'no'=>'required',
            'kode_produk' => 'required'
        ]);

        $temp =  new TransactionTemp();
        $temp->description = 'pln';
        $temp->save();

        $temp->transaction_id = date('dmyhis').$temp->id;
        $temp->save();

        $response = digiflazz([
            'commands'=>'pln-subscribe',
            'id'=>$temp->id,
            'type'=>'production', // Demo  or Production
            'product'=>$r->kode_produk,
            'no'=>$r->no,
            'action'=>'cek-tagihan-token',
            'ref_id'=>$temp->transaction_id
        ]);

        $product = Product::where('kode_produk',$r->kode_produk)->first();

        $data = [];
        $response = json_decode($response)->data;
        $data['customer_no'] = $response->customer_no;
        $data['name'] = $response->name; 
        $data['subscriber_id'] = $response->subscriber_id;
        $data['segment_power'] = $response->segment_power;
        $data['harga'] = "Rp. ".format_idr($product->harga_jual+$this->biaya_admin);
        $data['keterangan'] = $product->keterangan;
        $data['biaya_admin'] = "Rp. ".format_idr($this->biaya_admin);
       
        return response()->json(['message'=>'success','data'=>$data]);
    }

    public function getToken()
    {
        $data = [];
        foreach(Product::where(['product_type_id'=>10,'product_operator_id'=>1])->get() as $k => $item){
            $data[$k]['kode_produk'] = $item->kode_produk;
            $data[$k]['keterangan'] = $item->keterangan;
            $data[$k]['harga'] = format_idr($item->harga_jual+$this->biaya_admin);
        }
        return response()->json(['message'=>'success','data'=>$data]);
    }
}
