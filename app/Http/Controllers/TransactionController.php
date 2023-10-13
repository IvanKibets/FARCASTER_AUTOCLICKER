<?php

namespace App\Http\Controllers;
use App\Models\PulsaOperatorPrefix;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Koperasi;
use App\Jobs\HandleRequestJob;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public $status='success',$data;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['callbackDigiflazz','callbackIak']]);
    }

    public function qrcode(Request $r)
    {
        $this->validate($r,[
            'no_koperasi'=>'required'
        ]);

        $koperasi = Koperasi::where('no_koperasi',$r->no_koperasi)->first();
        
        if($koperasi){
            $this->data['nama'] = $koperasi->name;
        }else {
            $this->status = 'failed';
            $this->data = "Data tidak ditemukan silahkan dicoba kembali.";
        }

        return response()->json(['status'=>$this->status,'data'=>$this->data]);
    }


    public function qrcodeSubmit(Request $r)
    {
        $this->validate($r,[
            'no_koperasi'=>'required',
            'amount'=>'required',
            'metode_pembayaran'=>'required'
        ]);

        $r->amount = clear_currency($r->amount);
        $koperasi = Koperasi::where('no_koperasi',$r->no_koperasi)->first();
        
        if($koperasi){
            $params['url'] = $koperasi->url.'/api/transaction/submit-qrcode';
            $params['token'] = $koperasi->token;
            $params['no_anggota'] = \Auth::user()->no_anggota;
            $params['amount'] = $r->amount;
            $params['metode_pembayaran'] = $r->metode_pembayaran;

            $sinkron = sinkronKoperasi($params);
            
            if($sinkron->status()==200){
                $response = json_decode($sinkron->body());
                $this->status = $response->status ? 'success' : 'failed';
                $this->data = $response->message;
            }

        }else {
            $this->status = 'failed';
            $this->data = "Data tidak ditemukan silahkan dicoba kembali.";
        }

        return response()->json(['status'=>$this->status,'data'=>$this->data]);
    }

    public function data(Request $r)
    {
        $tahun = $r->filter_tahun=="" ? date('Y') : $r->filter_tahun ;
        $k_bulan=0;
        $data = [];
        foreach([1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'] as $bulan_id => $bulan_name){
            $find = Transaction::where(['user_id'=>\Auth::user()->id,'transaction_type'=>1])->whereMonth('created_at',$bulan_id)->whereYear('created_at',$tahun)->orderBy('id','DESC')->paginate(100);
            $data[$k_bulan]['id']= $bulan_id;
            $data[$k_bulan]['name']= $bulan_name;
            $data[$k_bulan]['tahun']= $tahun;
            $data[$k_bulan]['label']= $bulan_name .' '. $tahun;
            $data[$k_bulan]['data'] = [];

            foreach($find as $k => $item){
                $data[$k_bulan]['data'][$k]['id'] = $item->id;
                $data[$k_bulan]['data'][$k]['no_transaksi'] = $item->transaction_id;
                $data[$k_bulan]['data'][$k]['description'] = $item->description ? $item->description : '-';
                $data[$k_bulan]['data'][$k]['status'] = status($item->status);
                $data[$k_bulan]['data'][$k]['price'] = "Rp. ".format_idr($item->price);
                $data[$k_bulan]['data'][$k]['date'] = date('d M Y',strtotime($item->created_at));
                $data[$k_bulan]['data'][$k]['items'] = '';
                $data[$k_bulan]['data'][$k]['metode_pembayaran'] = metode_pembayaran($item->metode_pembayaran);
                $data[$k_bulan]['data'][$k]['data_json'] = json_decode($item->data_json);
                $data[$k_bulan]['data'][$k]['keterangan'] = $item->keterangan_gagal ? $item->keterangan_gagal : '-';
                $type_produk = '-';
                if(isset($item->items)){
                    foreach($item->items as $i){
                        $data[$k_bulan]['data'][$k]['items'] .= $i->description;
                        $type_produk = isset($i->product->type) ? $i->product->type : '-'; 
                    }
                }
                $data[$k_bulan]['data'][$k]['type_produk'] = $type_produk;
            }

            $k_bulan++;
        }
        
        return response()->json(['message'=>'success','data'=>$data,'user_id'=>\Auth::user()->id]);
    }

    public function callbackDigiflazz(Request $r)
    {   
        $body = $r->all();
        $find = Transaction::where('transaction_id',$body['data']['ref_id'])->first();
        $params = [];
        if($find){
            $find->api_response_after = json_encode($body);
            if($body['data']['rc']=='00'){
                if(isset($find->product->id)){    
                    /**
                     * Transaksi Token Listrik
                     */
                    if($find->product->product_type_id==10 and $find->product->product_operator_id==1){
                        $json = explode('/',$body['data']['sn']);
                        $find->data_json = json_encode([
                                'no_token'=>$json[0],
                                'no'=>$body['data']['customer_no'],
                                'nama'=>$json[1],
                                'kwh'=>$json[2].' '.$json[3]
                            ]);
                    }
                }

                $find->status = 1;
                $message = "";$type="";
                foreach($find->items as $i){
                    $message .= $i->description;
                }
                if(isset($find->user->device_token)){
                    push_notification_android($find->user->device_token,'Transaksi Berhasil',$message,1);
                }
            }else{
                $message = "";$type="";
                foreach($find->items as $i){
                    $message .= $i->description;
                }
                if(isset($find->user->device_token)){
                    push_notification_android($find->user->device_token,'Transaksi '. $body['data']['message'],$message,1);
                }

                $find->status = 2;
            }
            $find->save();
        }

        $params = 
            [
                'url'=>"/api/transaction-update",
                'transaction_type'=>1, // pulsa
                'transaction_id'=>$find->transaction_id,
                'status'=>$find->status,
                'api_response_after'=> $find->api_response_after,
                'data_json'=>$find->data_json
            ];

        $this->dispatch(new RequestKoperasi(\Auth::user(),$find,$params));
    }
}