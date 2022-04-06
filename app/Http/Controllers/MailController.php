<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmarSedeMailable;
use http\Env\Response;
use Illuminate\Http\Request;
use App\Mail\UpdatesMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class MailController extends Controller
{

    public function enviarEmail(Request $request){

        $data = $request->all();

        $validator = Validator::make($data, [
            'subject' => 'required|string',
            'body' => 'required|string',
            'receivers.*' => 'required|email:rfc',
            'files.*' => 'mimes:png,jpg,jpeg,bpm,png,pdf|max:2048'
        ]);

        if ($validator->fails()){

            return response()->json([
                'detalles' => $validator->errors(),
            ], 400);

        }else{

            $subject = $data['subject'];
            $body = $data['body'];
            $files = [];
            if (isset($data['files'])){
                $files = $data['files'];
            }

            try{
                Mail::to([])->bcc($data['receivers'])->send(new UpdatesMailable($subject, $body, $files));
            return response()->json([
                'details'=> 'Se han mandado los correos satisfactoriamente'
            ]);
            }catch(\Exception $e){

            }

        }

    }

}
