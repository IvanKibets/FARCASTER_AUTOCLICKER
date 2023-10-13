<?php

namespace App\Http\Controllers\Koperasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pinjaman;
use App\Models\Simpanan;
use App\Models\Koperasi;
use App\Models\User;
use App\Models\Transaction;

class SimpananController extends Controller
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

    public function store(Request $r)
    {
        $this->validate($r,[
            'token'=>'required',
            'jenis_simpanan_id'=>'required',
            'amount'=>'required',
            'tahun'=>'required',
            'bulan'=>'required',
            'no_anggota'=>'required'
        ]);

        $koperasi = Koperasi::where(['token'=>$r->token])->first();
        $user = User::where('no_anggota',$r->no_anggota)->first();

        if($koperasi and $user){
            $data = new Simpanan();
            $data->no_transaksi = $r->jenis_simpanan_id.date('my').str_pad((Simpanan::count()+1),4, '0', STR_PAD_LEFT);
            $data->description = $r->description;
            $data->jenis_simpanan_id = $r->jenis_simpanan_id;
            $data->amount = $r->amount;
            $data->user_id = $user->id;
            $data->status = $r->payment_date ? 1 : 0;
            if($r->payment_date) $data->payment_date = $r->payment_date;
            $data->tahun = $r->tahun;
            $data->bulan = $r->bulan;
            $data->koperasi_id = $koperasi->id;
            $data->save();
    
            $transaksi = new Transaction();
            $transaksi->transaction_id = "S".date('my').$user->id.str_pad((Transaction::count()+1),4, '0', STR_PAD_LEFT);
            $transaksi->user_id = $user->id;
            $transaksi->price = $r->amount;
            $transaksi->description = isset($data->jenis_simpanan->name) ? $data->jenis_simpanan->name :'-';
            $transaksi->koperasi_id = $koperasi->id;
            if($r->payment_date) {
                $transaksi->status = 1;
                $transaksi->payment_date = $r->payment_date;
            }else{
                $transaksi->status = 0;
            }
            
            $transaksi->save();
            
        }else $this->status = 'failed';
        
        return response()->json(['status'=>$this->status,'data'=>$r->all()], 200);
    }
}