<?php

namespace App\Http\Controllers;

use http\Env\Response;
use Illuminate\Http\Request;
use App\Mail\UpdatesMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class MailController extends Controller
{

    public function send(Request $request){

        $data = $request->all();

        $validator = Validator::make($data, [
            'subject' => 'required|string',
            'body' => 'required|string',
            'receivers.*' => 'required|email:rfc',
            'files.*' => 'mimes:png,jpg,jpeg,bpm,png,pdf|max:2048'
        ]);

        if ($validator->fails()){
            return $validator->errors();
        }else{

            $subject = $data['subject'];
            $body = $data['body'];

            try{
                Mail::to([])->bcc($data['receivers'])->send(new UpdatesMailable($subject, $body, $data));
                return response()->json([
                    'details'=> 'Se han mandado los correos satisfactoriamente'
                ]);
            }catch(\Exception $e){
                return response()->json([
                    'details'=> Mail::failures()
                ], 400);
            }

        }

    }

}
