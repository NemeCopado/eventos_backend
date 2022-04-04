<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\User;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('jwt', ['except' => ['login']]);
    }

    //LOGIN DE USUARIOS
    public function login(Request $request){

        $credentials = $request->only('email', 'password');

        $validator = Validator::make($credentials, [
            'email' => 'required|email:rfc',
            'password' => 'required'
        ]);

        if ($validator->fails()){

            return response()->json([
                'detalle'=>$validator->errors()
            ], 400);

        }else{

            try {

                if ( !$token = JWTAuth::attempt($credentials) ){

                    return response()->json([
                        'detalle'=> 'Credenciales invalidas'
                    ], 401);

                }

            }  catch (JWTException $e) {

                return response()->json([
                    'error' => 'could_not_create_token'
                ], 500);

            }

            return response()->json([
                'token'=> $token
            ]);

        }

    }

    public function getAuthenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
        return response()->json(compact('user'));
    }



}
