<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public $status='success';
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }
    
    public function data()
    {
        $temp = Notification::where('user_member_id',\Auth::user()->id)->orderBy('id','DESC')->paginate(100);
        $data = [];
        foreach($temp as $k => $item){
            $data[$k]['id'] = $item->id;
            $data[$k]['title'] = $item->title;
            $data[$k]['message'] = $item->message;
            $data[$k]['date'] = date('d-M-Y',strtotime($item->created_at));
        }
        return response()->json(['status'=>$this->status,'data'=>$data], 200);
    }
}