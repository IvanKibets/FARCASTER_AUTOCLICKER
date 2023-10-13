<?php

namespace App\Http\Controllers\Koperasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Koperasi;
use Illuminate\Support\Facades\Hash;

class NotifikasiController extends Controller
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
            'no_anggota'=>'required'
        ]);

        $koperasi = Koperasi::where(['token'=>$r->token])->first();
        $user = User::where('no_anggota',$r->no_anggota)->first();

        if($koperasi and $user){
            push_notification_android($user->device_token,$r->title,$r->message,4);
        
        }else $this->status = 'failed';
        
        return response()->json(['status'=>$this->status,'data'=>$r->all()], 200);
    }
}