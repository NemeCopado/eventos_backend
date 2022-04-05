<?php

namespace App\Http\Controllers;

use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Usuarios;
use App\Models\User;
use App\Models\Instituciones;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;


class InstitucionesController extends Controller
{

    //LISTADO DE INSTITUCIONES
    public function index(){

        //Select de los usuarios con relación de institución
        $instituciones = DB::table('usuarios')
            ->leftJoin('instituciones', 'usuarios.id_insti', 'instituciones.id_insti')
            ->leftjoin('municipios', 'instituciones.id_municipio', '=', 'municipios.id_municipio')
            ->select('instituciones.nombre as institucion',
                'instituciones.domicilio',
                'municipios.nombre as municipio',
                DB::raw('CONCAT(usuarios.nombre, " ", usuarios.ape_pat, " ", usuarios.ape_mat) AS enlace'))
            ->where('usuarios.activo', 1)
            ->where('instituciones.activo', 1)
            ->get();

        $json = array(
            "status"=>200,
            "total_registros"=>count($instituciones),
            "details"=>$instituciones
        );

        return json_encode($json, true);

    }

    //TOMAR UN SOLO REGISTRO
    public function show($id){

        $institucion = DB::table('usuarios')
            ->leftJoin('instituciones', 'usuarios.id_insti', 'instituciones.id_insti')
            ->leftjoin('municipios', 'instituciones.id_municipio', '=', 'municipios.id_municipio')
            ->select('instituciones.nombre as institucion',
                'instituciones.domicilio',
                'municipios.nombre as municipio',
                DB::raw('CONCAT(usuarios.nombre, " ", usuarios.ape_pat, " ", usuarios.ape_mat) AS enlace'))
            ->where('instituciones.id_insti', $id)
            ->where('usuarios.activo', 1)
            ->where('instituciones.activo', 1)
            ->get();

        if(!empty($institucion[0])){
            $json = array(
                "status" => 200,
                "details" => $institucion
            );
        }else{
            $json = array(
                "status" => 200,
                "details" => "No hay ninguna institución registrada con ese ID"
            );
        }
        return json_encode($json, true);

    }

    //ALTA DE INSTITUCIONES Y ENLACES
    public function store(Request $request){

        //Recolección de datos de los input
        $datos = array( "institucion_nombre"=>$request->input('institucion_nombre'),
            "domicilio"=>$request->input('domicilio'),
            "id_municipio"=>$request->input('id_municipio'),
            "usuario_nombre"=>$request->input('usuario_nombre'),
            "usuario_ape_pat"=>$request->input('usuario_ape_pat'),
            "usuario_ape_mat"=>$request->input('usuario_ape_mat'),
            "cargo"=>$request->input('cargo'),
            "rol"=>$request->input('rol'),
            "tel"=>$request->input('tel'),
            "email"=>$request->input('email'),
            "password"=>$request->input('password'),
            "activo"=>1,
            );

        //Validamos que el enlace que se quiere registrar ya haya estado registrado antes y que tenga eliminado lógico
        $usuario = Usuarios::where('email', $datos['email'])->where('activo', 0)->first();

        if ($usuario){

            //Validar formato de datos
            $validator = Validator::make($datos, [
                'institucion_nombre' => 'required|string',
                'domicilio'=> 'required|string',
                'id_municipio'=>'required|integer',
                'usuario_nombre'=>'required|string',
                'usuario_ape_pat'=>'required|string',
                'usuario_ape_mat'=>'required|string',
                'cargo'=>'required|string',
                'rol'=>'required|string',
                'tel'=>'required|integer',
                'email'=>'required|email:rfc',
                'password'=>'required|string',
            ]);

            //Si falla la validación del formato
            if ($validator -> fails()) {

                $errors = $validator->errors();
                return response()->json([
                    'detalles'=>$errors
                ], 400);

                //Si pasa la validación de formato, continuamos el proceso de reactivación
                // de la institución y su enlace y actualizamos con los nuevos datos proporcionados
            }else{

                if ($institucion = Instituciones::where('id_insti', $usuario->id_insti) -> update(
                    array('nombre'=>$datos['institucion_nombre'],
                        'domicilio'=>$datos['domicilio'],
                        'id_municipio'=>$datos['id_municipio'],
                        'activo'=>1)
                )){
                    if ($usuario = Usuarios::where('email', $datos['email'])->update(
                        array('nombre'=>$datos['usuario_nombre'],
                            'ape_pat'=>$datos['usuario_ape_pat'],
                            'ape_mat'=>$datos['usuario_ape_mat'],
                            'id_insti'=>DB::table('instituciones')->latest()->first()->id_insti,
                            'cargo'=>$datos['cargo'],
                            'rol'=>$datos['rol'],
                            'tel'=>$datos['tel'],
                            'password'=>Hash::make($datos['password']),
                            'activo'=>1,
                        )
                    )){

                        return response()->json([
                            'detalle'=>'Se ha reactivado la institución y el enlace y se han actualizado los datos'
                        ]);

                    }
                }

            }

        }else{

            //Validamos que no estén vacíos los datos ingresados por el usuario
            if(!empty($datos)){

                //Validar formato de datos
                $validator = Validator::make($datos, [
                    'institucion_nombre' => 'required|string',
                    'domicilio'=> 'required|string',
                    'id_municipio'=>'required|integer',
                    'usuario_nombre'=>'required|string',
                    'usuario_ape_pat'=>'required|string',
                    'usuario_ape_mat'=>'required|string',
                    'cargo'=>'required|string',
                    'rol'=>'required|string',
                    'tel'=>'required|integer',
                    'email'=>'required|email:rfc|unique:usuarios,email',
                    'password'=>'required|string',
                ]);

                //Si falla la validación del formato
                if ($validator -> fails()) {

                    $errors = $validator->errors();
                    return response()->json([
                        'detalles'=>$errors
                    ], 400);

                    //Si pasa la validación de formato, continuamos el proceso
                }else{

                    $instituciones = new Instituciones();
                    $instituciones->nombre=$datos["institucion_nombre"];
                    $instituciones->domicilio=$datos["domicilio"];
                    $instituciones->id_municipio=$datos["id_municipio"];
                    $instituciones->activo=1;

                    if($instituciones->save()){

                        $usuarios = new Usuarios();
                        $usuarios->nombre=$datos["usuario_nombre"];
                        $usuarios->ape_pat=$datos["usuario_ape_pat"];
                        $usuarios->ape_mat=$datos["usuario_ape_mat"];
                        $usuarios->id_insti=$instituciones->id;
                        $usuarios->cargo=$datos["cargo"];
                        $usuarios->rol=$datos["rol"];
                        $usuarios->tel=$datos["tel"];
                        $usuarios->email=$datos["email"];
                        $usuarios->password=Hash::make($datos["password"]);
                        $usuarios->activo=1;

                        if ($usuarios->save()){

                            $user = User::create([
                                'name' => $datos["usuario_nombre"] . ' ' . $datos["usuario_ape_pat"] . ' ' . $datos["usuario_ape_mat"],
                                'email' => $datos["email"],
                                'password' => Hash::make($datos["password"]),
                            ]);
                            $token = JWTAuth::fromUser($user);
                            return response()->json(compact('user','token'),201);

                        }

                    }else{

                        return response()->json([
                            'detalles'=>'Error en el registro de la institución',
                        ], 400);

                    }

                }

                //Si está vacío el arreglo, retornamos status de error
            }else{

                return response()->json([
                    'detalles'=>'Los registros no pueden estar vacíos',
                ], 400);

            }

        }

    }

    //ELIMINADO LÓGICO DE INSTITUCIÓN Y ENLACE
    public function destroy($id, Request $request){

        //Buscamos la institución requerida por el usuario para eliminado lógico
        $institucion = DB::table('usuarios')
            ->leftJoin('instituciones', 'usuarios.id_insti', 'instituciones.id_insti')
            ->leftjoin('municipios', 'instituciones.id_municipio', '=', 'municipios.id_municipio')
            ->select('instituciones.nombre as institucion',
                'instituciones.domicilio',
                'municipios.nombre as municipio',
                DB::raw('CONCAT(usuarios.nombre, " ", usuarios.ape_pat, " ", usuarios.ape_mat) AS enlace'))
            ->where('instituciones.id_insti', $id)
            ->get();

        //Si existe la institución solicitada continuamos con la eliminación
        if(!empty($institucion[0])){

            $id_user = DB::table('usuarios')->where('id_insti', $id)->value('id_user');
//            if (Usuarios::where('id_user', $id_user)->delete()){
            if (DB::table('usuarios')->where('id_user', $id_user)->update(['activo'=>0])){
//                if (DB::table('instituciones')->where('id_insti', "=", $id)->delete()){
                if(DB::table('instituciones')->where('id_insti', $id)->update(['activo'=>0])){
                    $json = array(
                        "status" => 200,
                        "details" => "Se eliminó la institución y su enlace satisfactoriamente"
                    );
                    return json_encode($json, true);
                }else{
                    $json = array(
                        "status" => 200,
                        "details" => "Hubo un problema al eliminar la institución"
                    );
                    return json_encode($json, true);
                }
            }else{
            $json = array(
                "status" => 200,
                "details" => "Hubo un problema al eliminar al usuario"
            );
            return json_encode($json, true);
            }

        //Si no existe la institución solicitada mandamos mensaje del status
        }else{
            $json = array(
                "status" => 200,
                "details" => "No hay existe alguna institución registrada con ese Id"
            );
        }
        return json_encode($json, true);

    }


}
