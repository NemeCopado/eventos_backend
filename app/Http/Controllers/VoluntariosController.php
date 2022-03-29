<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoluntariosController extends Controller
{

    public function index(){

        //Select de los usuarios con relación de institución
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

        $institucion = DB::table('instituciones')
            ->where('id_insti', $id_institucion)
            ->where('activo', 1)
            ->get();

        if(!empty($institucion[0])){

            $voluntarios = DB::table('voluntarios')
                ->where('id_insti', $id_institucion)
                ->where('activo', 1)
                ->get();

            $json = array(
                "status"=>200,
                "total_registros"=>count($voluntarios),
                "details"=>$voluntarios
            );

            return json_encode($json, true);

        }else{

            $json = array(
                "status"=>200,
                "details"=>'No hay ninguna institución registrada con esa Id'
            );

            return json_encode($json, true);

        }

    }

}
