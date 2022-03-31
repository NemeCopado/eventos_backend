<?php

namespace App\Http\Controllers;

use App\Models\Sedes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SedesController extends Controller
{

    //Listado de sedes
    public function index(){

        $sedes = Sedes::all();

        $json = array(
            "status" => 404,
            "detalles" => $sedes,
        );

        return json_encode($json, true);

    }

    //Alta de una sede
    public function store(Request $request){

        //Recolección de datos de los input
        $datos = array( "id_municipio"=>$request->input('id_municipio'),
            "nombre"=>$request->input('nombre'),
            "direccion"=>$request->input('direccion'),
            "cruce_calles"=>$request->input('cruce_calles'),
            "colonia"=>$request->input('colonia'),
            "cp"=>$request->input('cp'),
            "latitud"=>$request->input('latitud'),
            "longitud"=>$request->input('longitud'),
            "georeferencia"=>$request->input('georeferencia'),
            "nombre_encargado"=>$request->input('nombre_encargado'),
            "tel_encargado"=>$request->input('tel_encargado'),
            "email_encargado"=>$request->input('email_encargado'),
            "cupo"=>$request->input('cupo'),
        );

        //Validamos que no estén vacíos los datos ingresados por el usuario
        if(!empty($datos)){

            //Validar formato de datos
            $validator = Validator::make($datos, [
                'id_municipio' => 'required|integer',
                'nombre'=> 'required|string',
                'direccion'=>'required|string',
                'cruce_calles'=>'required|string',
                'colonia'=>'required|string',
                'cp'=>'required|integer',
//                'latitud'=>'string',
//                'longitud'=>'string',
//                'georeferencia'=>'string',
                'nombre_encargado'=>'required|string',
                'tel_encargado'=>'required|integer',
                'email_encargado'=>'required|email:rfc',
                'cupo'=>'required|integer',
            ]);

            //Si falla la validación del formato
            if ($validator -> fails()) {

                $errors = $validator->errors();

                $json = array(
                    "status" => 404,
                    "detalles" => $errors
                );
                return json_encode($json, true);

            //Si pasa la validación de formato, continuamos el proceso
            }else{

                $sede = new Sedes();
                $sede->id_municipio=$datos["id_municipio"];
                $sede->nombre=$datos["nombre"];
                $sede->direccion=$datos["direccion"];
                $sede->cruce_calles=$datos["cruce_calles"];
                $sede->colonia=$datos["colonia"];
                $sede->cp=$datos["cp"];
                $sede->latitud=$datos["latitud"];
                $sede->longitud=$datos["longitud"];
                $sede->georeferencia=$datos["georeferencia"];
                $sede->nombre_encargado=$datos["nombre_encargado"];
                $sede->tel_encargado=$datos["tel_encargado"];
                $sede->email_encargado=$datos["email_encargado"];
                $sede->activo=1;
                $sede->cupo=$datos["cupo"];

                if ($sede->save()){
                    $json = array(
                        "status" => 200,
                        "detalles" => "Se registro la sede satisfactoriamente",
                    );
                    return json_encode($json, true);
                }else{
                    $json = array(
                        "status" => 404,
                        "detalles" => "Ocurrió un error al registrar la sede",
                    );
                    return json_encode($json, true);
                }

            }

        }


    }

    //Baja de una sede
    public function destroy($id_sede){

        //Elimnado lógico de la sede
        if (DB::table('sedes')->where('id_sede', $id_sede)->update(['activo'=>0])){
            $json = array(
                "status" => 200,
                "details" => "Se eliminó la satisfactoriamente."
            );
            return json_encode($json, true);
        }else{
            $json = array(
                "status" => 404,
                "details" => "Error al eliminar la sede solicitada."
            );
            return json_encode($json, true);
        }

    }

    //Actualización de una sede
    public function update($id_sede, Request $request){

        //Listamos si existe el ID solicitado
        $sede = DB::table('sedes')
            ->where('sedes.id_sede', $id_sede)
            ->get();

        //En caso de que sí exista continuamos con la edición del usuario
        if(!empty($sede[0])){

            //Recolección de datos de los input
            $datos = array( "id_municipio"=>$request->input('id_municipio'),
                "nombre"=>$request->input('nombre'),
                "direccion"=>$request->input('direccion'),
                "cruce_calles"=>$request->input('cruce_calles'),
                "colonia"=>$request->input('colonia'),
                "cp"=>$request->input('cp'),
                "latitud"=>$request->input('latitud'),
                "longitud"=>$request->input('longitud'),
                "georeferencia"=>$request->input('georeferencia'),
                "nombre_encargado"=>$request->input('nombre_encargado'),
                "tel_encargado"=>$request->input('tel_encargado'),
                "email_encargado"=>$request->input('email_encargado'),
                "cupo"=>$request->input('cupo'),
            );

            //Validar formato de datos
            $validator = Validator::make($datos, [
                'id_municipio' => 'required|integer',
                'nombre'=> 'required|string',
                'direccion'=>'required|string',
                'cruce_calles'=>'required|string',
                'colonia'=>'required|string',
                'cp'=>'required|integer',
                'latitud'=>'string',
                'longitud'=>'string',
                'georeferencia'=>'string',
                'nombre_encargado'=>'required|string',
                'tel_encargado'=>'required|integer',
                'email_encargado'=>'required|email:rfc',
                'cupo'=>'required|integer',
            ]);

            //Si falla la validación del formato
            if ($validator -> fails()) {

                $errors = $validator->errors();

                $json = array(
                    "status" => 404,
                    "detalles" => $errors
                );

                return json_encode($json, true);

            }else{

                $datos = array( "id_municipio"=>$datos['id_municipio'],
                    "nombre"=>$datos['nombre'],
                    "direccion"=>$datos['direccion'],
                    "cruce_calles"=>$datos['cruce_calles'],
                    "colonia"=>$datos['colonia'],
                    "cp"=>$datos['cp'],
                    "latitud"=>$datos['latitud'],
                    "longitud"=>$datos['longitud'],
                    "georeferencia"=>$datos['georeferencia'],
                    "nombre_encargado"=>$datos['nombre_encargado'],
                    "tel_encargado"=>$datos['tel_encargado'],
                    "email_encargado"=>$datos['email_encargado'],
                    "cupo"=>$datos['cupo'],
                );

                //Actualizamos el registro
                if ($sede = Sedes::where('id_sede', $id_sede) -> update($datos)){

                    $json = array(
                        "status" => 200,
                        "detalles" => "Registro actualizado exitosamente"
                    );

                    return json_encode($json, true);

                }else{

                    $json = array(
                        "status" => 404,
                        "detalles" => "Error al actualizar el registro."
                    );

                    return json_encode($json, true);

                }

            }

        }else{

            $json = array(
                "status" => 404,
                "details" => "No hay ninguna sede registrada con ese Id"
            );

            return json_encode($json, true);

        }

    }


}
