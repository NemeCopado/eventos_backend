<?php

namespace App\Http\Controllers;

use App\Models\Detalle_Jornadas;
use App\Models\Sedes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Voluntarios;

class VoluntariosController extends Controller
{

    //Listado de voluntarios
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

    //Eliminación de un voluntario
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

    //Listar el registro de todos los voluntarios por institución
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

    //Registro de voluntarios
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

                $voluntario = new Voluntarios();
                $voluntario->nombre=$datos["nombre"];
                $voluntario->ape_pat=$datos["ape_pat"];
                $voluntario->ape_mat=$datos["ape_mat"];
                $voluntario->id_insti=$datos["id_insti"];
                $voluntario->curp=$datos["curp"];
                $voluntario->email=$datos["email"];
                $voluntario->tel=$datos["tel"];
                $voluntario->fecha_nacimiento=$datos["fecha_nacimiento"];
                $voluntario->id_municipio=$datos["id_municipio"];
                $voluntario->activo=1;
                $voluntario->eliminado=0;

                if ($voluntario->save()){
                    $json = array(
                        "status" => 200,
                        "detalles" => "Se registro el voluntario satisfactoriamente "."con Id: ".$voluntario->id,
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

    //Asignación de sedes a voluntarios
    public function asignarSede(Request $request){

        //Recolección de datos de los input
        $datos = array( "id_jornada"=>$request->input('id_jornada'),
            "id_voluntario"=>$request->input('id_voluntario'),
            "id_sede"=>$request->input('id_sede'),
            "turno"=>$request->input('turno'),
            "uuid"=>$request->input('uuid'),
            "horas"=>$request->input('horas'),
            "activo"=>$request->input('activo'),
        );

        //Validamos que no estén vacíos los datos ingresados por el usuario
        if(!empty($datos)){

            //Validar formato de datos
            $validator = Validator::make($datos, [
                'id_jornada' => 'required|integer',
                'id_voluntario'=> 'required|integer',
                'id_sede'=>'required|integer',
                'turno'=>'required|string',
                'uuid'=>'required|string|string',
                'horas'=>'required|integer',
                'activo'=>'required|integer',
            ]);

            if ($validator -> fails()) {

                $errors = $validator->errors();

                $json = array(
                    "status" => 404,
                    "detalles" => $errors
                );
                return json_encode($json, true);

                //Si pasa la validación de formato, continuamos el proceso
            }else{

                //Buscamos la información del voluntario
                $voluntario = Voluntarios::where('id_voluntario', $datos['id_voluntario'])->first();
                //Buscamos la información de la sede
                $sede = Sedes::where('id_sede', $datos['id_sede'])->first();

                //Si el municipio de la sede coincide con el municipio del voluntario registrado continúa el proceso
                if ($voluntario->id_municipio == $sede -> id_municipio){

                    $detalle_jornada = new Detalle_Jornadas();
                    $detalle_jornada -> id_jornada = $datos['id_jornada'];
                    $detalle_jornada -> id_voluntario = $datos['id_voluntario'];
                    $detalle_jornada -> id_sede = $datos['id_sede'];
                    $detalle_jornada -> turno = $datos['turno'];
                    $detalle_jornada -> uuid = $datos['uuid'];
                    $detalle_jornada -> horas = $datos['horas'];
                    $detalle_jornada -> correo_enviado = 1;
                    $detalle_jornada -> eliminado = 0;
                    $detalle_jornada -> activo = $datos['activo'];

                    if($detalle_jornada->save()){
                        $json = array(
                            "status" => 200,
                            "detalles" => 'Se ha hecho la asignación al voluntario exitosamente.'
                        );
                        return json_encode($json, true);
                    }

                //Retornamos error de municipios distintos
                }else{

                    $json = array(
                        "status" => 404,
                        "detalles" => 'El municipio de la sede no coincide con el municipio del voluntario registrado'
                    );
                    return json_encode($json, true);

                }

            }

        }

    }


}
