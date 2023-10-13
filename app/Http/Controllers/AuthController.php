<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use  App\Models\User;
use  App\Models\UserAjrius;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'refresh', 'logout']]);
    }
    
    /**
     * Get a JWT via given credentials.
     *
     * @param  Request  $request
     * @return Response
     */
    public function login(Request $request)
    {
        log_activity('Submit Login');

        $this->validate($request, [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['username', 'password']);

        $device_token = $request->device_token;

        if (! $token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 200);
        }

        $user = User::find(\Auth::user()->id);
        $user->device_token = $device_token;
        $user->save();

        return $this->respondWithToken($token);
    }

    public function changePassword(Request $r)
    {
        $result = ['status'=>'success'];
        if(!Hash::check($r->old_password, \Auth::user()->password)){
            $result['status'] = 'error';
            $result['message'] = 'Password yang anda masukan salah, silahkan dicoba kembali !';
        }elseif($r->new_password!=$r->confirm_new_password){
            $result['status'] = 'error';
            $result['message'] = 'Konfirmasi password salah silahkan dicoba kembali !';
        }else{
            $user = \Auth::user();
            $user->password = Hash::make($r->new_password);
            $user->save();
            $result['message'] = 'Password berhasil dirubah !';
        }

        log_activity('Change Password');
        
        return response()->json($result, 200);
    }

     /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        log_activity('Get Profile');
        
        return response()->json(['message'=>'success','data']);
    }

    public function get_profile()
    {
        $user = Auth::user();
        
        $data['name'] = $user->nama ? $user->name : '-';
        $data['email'] = $user->email ? $user->email : '-';

        return $data;
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        log_activity('Logout');

        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $r)
    {
        log_activity('Refresh Token');

        $user = auth()->user();
        if($user)
            return $this->respondWithToken($r->token);
        else
            return response()->json(['message'=>'failed','code'=>200],200);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'message'=>'success',
            'access_token' => $token,
            'token_type' => 'bearer',
            'data' => $this->get_profile(),
            'expires_in' => auth()->factory()->getTTL() * 60 * 24
        ]);
    }
}
