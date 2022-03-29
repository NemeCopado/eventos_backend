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

}
