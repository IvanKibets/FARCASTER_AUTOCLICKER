<?php

namespace App\Http\Controllers;

use App\Models\Koperasi;
use Illuminate\Http\Request;

class KoperasiController extends Controller
{
    public $status="success",$message,$response=[];
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['getData']]);
    }

    public function getData()
    {
        $data = [];
        foreach(Koperasi::get() as $k => $item){
            $data[$k]['id'] = $item->id;
            $data[$k]['name'] = $item->name;
            $data[$k]['url'] = $item->url;
        }
        
        return response()->json(['status'=>'success','data'=>$data]);
    }
}
