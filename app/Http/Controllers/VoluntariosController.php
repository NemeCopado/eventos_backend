<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Voluntarios;

class VoluntariosController extends Controller
{

    public function index(){

        //Select de los voluntarios
        $voluntarios = DB::table('usuarios')
            ->leftJoin('instituciones', 'usuarios.id_insti', 'instituciones.id_insti')
            ->leftjoin('voluntarios', 'usuarios.id_insti', '=', 'voluntarios.id_insti')
            ->select(DB::raw('CONCAT(voluntarios.nombre, " ", voluntarios.ape_pat, " ", voluntarios.ape_mat) AS voluntario'),
                'instituciones.nombre as institucion',
                DB::raw('CONCAT(usuarios.nombre, " ", usuarios.ape_pat, " ", usuarios.ape_mat) AS enlace'),
                'voluntarios.created_at as fecha_registro'
            )
            ->where('usuarios.activo', 1)
            ->where('instituciones.activo', 1)
            ->where('voluntarios.activo', 1)
            ->get();

        $json = array(
            "status"=>200,
            "total_registros"=>count($voluntarios),
            "details"=>$voluntarios
        );

        return json_encode($json, true);

    }

    public function destroy($id_voluntario){

        //Update de activo y eliminado
        if (DB::table('voluntarios')->where('id_voluntario', $id_voluntario)->update(['activo'=>0, 'eliminado'=>1])){
            $json = array(
                "status" => 200,
                "details" => "Se eliminó el voluntario satisfactoriamente"
            );
            return json_encode($json, true);
        }else{
            $json = array(
                "status" => 200,
                "details" => "Error al eliminar el voluntario"
            );
            return json_encode($json, true);
        }

    }

    public function show($id_institucion){

        //Select a la tabla institución con el id requerido
        $institucion = DB::table('instituciones')
            ->where('id_insti', $id_institucion)
            ->where('activo', 1)
            ->get();

        //Verificamos que exista registro de institución con el id requerido
        if(!empty($institucion[0])){

            //Buscamos a todos los voluntarios pertenecientes a la institución solicitada
            $voluntarios = DB::table('voluntarios')
                ->where('id_insti', $id_institucion)
                ->where('activo', 1)
                ->get();

            //Retornamos los datos arrojados
            $json = array(
                "status"=>200,
                "total_registros"=>count($voluntarios),
                "details"=>$voluntarios
            );

            return json_encode($json, true);

        //En caso de no existir la institución solicitada mostramos mensaje
        }else{

            $json = array(
                "status"=>200,
                "details"=>'No hay ninguna institución registrada con esa Id'
            );

            return json_encode($json, true);

        }

    }

    public function store(Request $request){

        //Recolección de datos de los input
        $datos = array( "nombre"=>$request->input('nombre'),
            "ape_pat"=>$request->input('ape_pat'),
            "ape_mat"=>$request->input('ape_mat'),
            "id_insti"=>$request->input('id_insti'),
            "curp"=>$request->input('curp'),
            "email"=>$request->input('email'),
            "tel"=>$request->input('tel'),
            "fecha_nacimiento"=>$request->input('fecha_nacimiento'),
            "id_municipio"=>$request->input('id_municipio'),
        );

        //Validamos que no estén vacíos los datos ingresados por el usuario
        if(!empty($datos)){

            //Validar formato de datos
            $validator = Validator::make($datos, [
                'nombre' => 'required|string',
                'ape_pat'=> 'required|string',
                'ape_mat'=>'required|string',
                'id_insti'=>'required|integer',
                'curp'=>'required|string|unique:voluntarios,curp',
                'email'=>'required|email:rfc|unique:voluntarios,email',
                'tel'=>'required|integer',
                'fecha_nacimiento'=>'required|string',
                'id_municipio'=>'required|integer',
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

                $voluntarios = new Voluntarios();
                $voluntarios->nombre=$datos["nombre"];
                $voluntarios->ape_pat=$datos["ape_pat"];
                $voluntarios->ape_mat=$datos["ape_mat"];
                $voluntarios->id_insti=$datos["id_insti"];
                $voluntarios->curp=$datos["curp"];
                $voluntarios->email=$datos["email"];
                $voluntarios->tel=$datos["tel"];
                $voluntarios->fecha_nacimiento=$datos["fecha_nacimiento"];
                $voluntarios->id_municipio=$datos["id_municipio"];
                $voluntarios->activo=1;
                $voluntarios->eliminado=0;

                if ($voluntarios->save()){
                    $json = array(
                        "status" => 200,
                        "detalles" => "Se registro el voluntario satisfactoriamente "."con Id: ".$voluntarios->id,
                    );
                    return json_encode($json, true);
                }else{
                    $json = array(
                        "status" => 404,
                        "detalles" => "Ocurrió un error al registrar al voluntario",
                    );
                    return json_encode($json, true);
                }

            }


        }


    }

}
