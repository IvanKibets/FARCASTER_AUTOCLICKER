<?php

namespace App\Http\Controllers\Koperasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Koperasi;
use App\Models\Pinjaman;

class PinjamanController extends Controller
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

    public function approve(Request $r)
    {
        $this->validate($r,[
            'token'=>'required',
            'no_pengajuan'=>'required'
        ]);

        $koperasi = Koperasi::where(['token'=>$r->token])->first();

        if($koperasi){
            $pinjaman = Pinjaman::where('no_pengajuan',$r->no_pengajuan)->first();
            $pinjaman->status = 1;
            $pinjaman->save();

            if(isset($pinjaman->user->device_token)) push_notification_android($pinjaman->user->device_token,'Pengajuan Tunai',"Pengajuan Tunai kamu sebesar Rp. ".format_idr($pinjaman->amount).", disetujui silahkan datang ke Koperasi untuk pengambilan dana",5);
           
        }else $this->status = 'failed';
        
        return response()->json(['status'=>$this->status,'data'=>$r->all()], 200);
    }

    public function reject(Request $r)
    {
        $this->validate($r,[
            'token'=>'required',
            'no_pengajuan'=>'required'
        ]);

        $koperasi = Koperasi::where(['token'=>$r->token])->first();

        if($koperasi){
            $pinjaman = Pinjaman::where('no_pengajuan',$r->no_pengajuan)->first();
            $pinjaman->status = 3;
            $pinjaman->save();

            if(isset($pinjaman->user->device_token)) push_notification_android($pinjaman->user->device_token,'Pengajuan Tunai',"Mohon Maaf, Pengajuan Tunai kamu sebesar Rp. ".format_idr($pinjaman->amount).", belum dapat disetujui",5);
           
        }else $this->status = 'failed';
        
        return response()->json(['status'=>$this->status,'data'=>$r->all()], 200);
    }
}