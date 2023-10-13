<?php

namespace App\Http\Controllers\Koperasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Koperasi;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
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

    public function insert(Request $r)
    {
        $this->validate($r,[
            'token'=>'required',
            'data'=>'required',
        ]);

        $koperasi = Koperasi::where(['token'=>$r->token])->first();

        if($koperasi){
            $user  = new User();
            $user->password = Hash::make('12345678');
            $user->name = $r->data['nama'];
            $user->username = $r->data['no_anggota'];
            $user->no_anggota = $r->data['no_anggota'];
            $user->phone_number = $r->data['no_telepon'];
            $user->koperasi_id = $koperasi->id;
            $user->save();

        }else $this->status = 'failed';
        
        return response()->json(['status'=>$this->status,'data'=>$r->all()], 200);
    }

    public function edit(Request $r)
    {
        $this->validate($r,[
            'token'=>'required',
            'field'=>'required',
            'value'=>'required',
        ]);

        $koperasi = Koperasi::where(['token'=>$r->token])->first();

        if($koperasi){
            $user = User::where(['no_anggota'=>$r->no_anggota,'koperasi_id'=>$koperasi->id])->first();
            if(!$user){
                $user = new User();
                $user->no_anggota = $r->no_anggota;
                $user->username = $r->no_anggota;
                $user->password = Hash::make('12345678');
                $user->koperasi_id = $koperasi->id;
            }

            if($r->field=='no_anggota_platinum') 
                $field = 'no_anggota';
            else
                $field = $r->field;

            $user->$field = $r->value;
            $user->save();
        }else $this->status = 'failed';
        
        return response()->json(['status'=>$this->status,'data'=>$r->all()], 200);
    }
}