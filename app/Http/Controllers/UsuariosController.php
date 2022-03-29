<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuarios;
use App\Models\Instituciones;
use Illuminate\Support\Facades\DB;

class UsuariosController extends Controller
{

    //LISTADO DE USUARIOS
    public function index(){

        //Select de los usuarios con relación de institución
        $usuarios = DB::table('usuarios')
            ->leftjoin('instituciones', 'usuarios.id_insti', '=', 'instituciones.id_insti')
            ->select(DB::raw('CONCAT(usuarios.nombre, " ", usuarios.ape_pat, " ", usuarios.ape_mat) AS nombre_completo'),
                'usuarios.cargo',
                'usuarios.rol',
                'instituciones.nombre as institucion',
                'usuarios.tel as telefono',
                'usuarios.email as correo_electronico',
                'usuarios.activo')
            ->where('usuarios.activo', 1)
            ->where('instituciones.activo', 1)
            ->paginate(15);

        $json = array(
            "status"=>200,
            "total_registros"=>count($usuarios),
            "details"=>$usuarios
        );

        return json_encode($json, true);

    }

    //TOMAR UN SOLO REGISTRO
    public function show($id){

        $usuario = DB::table('usuarios')
            ->leftjoin('instituciones', 'usuarios.id_insti', '=', 'instituciones.id_insti')
            ->select('usuarios.nombre', 'usuarios.ape_pat as apellido_paterno', 'usuarios.ape_mat as apellido_materno',
                'usuarios.cargo',
                'usuarios.rol',
                'instituciones.nombre as institucion',
                'usuarios.tel as telefono',
                'usuarios.email as correo_electronico',
                'usuarios.activo')
            ->where('usuarios.id_user', $id)
            ->where('usuarios.activo', 1)
            ->where('instituciones.activo', 1)
            ->get();

        if(!empty($usuario[0])){
            $json = array(
                "status" => 200,
                "details" => $usuario
            );
        }else{
            $json = array(
                "status" => 200,
                "details" => "No hay ningún usuario registrado con ese ID"
            );
        }
        return json_encode($json, true);

    }

    //EDITAR USUARIOS
    public function update($id, Request $request){

        //Listamos si existe el ID solicitado
        $usuario = DB::table('usuarios')
            ->where('usuarios.id_user', $id)
            ->get();

        //En caso que sí existe continuamos con la edición del usuario
        if(!empty($usuario[0])){

            //Recogemos datos
            $datos = array( "nombre"=>$request->input('nombre'),
                "ape_pat"=>$request->input('ape_pat'),
                "ape_mat"=>$request->input('ape_mat'),
                "cargo"=>$request->input('cargo'),
                "rol"=>$request->input('rol'),
                "id_insti"=>$request->input('id_insti'),
                "tel"=>$request->input('tel'),
                "email"=>$request->input('email'),
                "activo"=>$request->input('activo'));

            //Validamos que no estén vacíos los datos ingresados por el usuario
            if(!empty($datos)){

                //Validar formato de datos
                $validator = Validator::make($datos, [
                    'nombre' => 'required|string',
                    'ape_pat' => 'required|string',
                    'ape_mat' => 'required|string',
                    'cargo' => 'required|string',
                    'rol' => 'required|string',
                    'id_insti' => 'required|integer',
                    'tel' => 'required|string',
                    'email' => 'required|string',
                    'activo' => 'required|integer'
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


                    $datos = array( "nombre"=>$datos['nombre'],
                        "ape_pat"=>$datos['ape_pat'],
                        "ape_mat"=>$datos['ape_mat'],
                        "cargo"=>$datos['cargo'],
                        "rol"=>$datos['rol'],
                        "id_insti"=>$datos['id_insti'],
                        "tel"=>$datos['tel'],
                        "email"=>$datos['email'],
                        "activo"=>$datos['activo']);

                    //Actualizamos el registro
                    $usuarios = Usuarios::where('id_user', $id) -> update($datos);

                    $json = array(
                        "status" => 200,
                        "detalles" => "Registro actualizado exitosamente"
                    );

                    return json_encode($json, true);

                }

            }else{

                $json = array(
                    "status" => 404,
                    "detalles" => "Los registros no pueden estar vacíos"
                );

                return json_encode($json, true);

            }
        }else{

            $json = array(
                "status" => 200,
                "details" => "No hay ningún usuario registrado con ese ID"
            );

        }

        return json_encode($json, true);

    }


}
