<?php

namespace App\Http\Controllers;

use App\Models\Detalle_Jornadas;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;

class Confirmacion extends Controller
{

    public function confirmarSede($id_detalle_jornada){

        $detalle_jornada = Detalle_Jornadas::where('id_detalle_jornada', $id_detalle_jornada)->first();
        $status = $detalle_jornada['activo'];
        if ($status === NULL) {
            if (Detalle_Jornadas::where('id_detalle_jornada', $id_detalle_jornada)->update(['activo' => 1])) {
                return response()->json([
                    'detalles' => 'Se ha registrado tu confirmación a la jornada satisfactoriamente'
                ]);
            }
        }else {
            return response()->json([
                'detalles' => 'Este enlace ya no es válido'
            ], 400);
        }

    }

    public function rechazarSede($id_detalle_jornada){

        $detalle_jornada = Detalle_Jornadas::where('id_detalle_jornada', $id_detalle_jornada)->first();
        $status = $detalle_jornada['activo'];
        if ($status === NULL) {
            if (Detalle_Jornadas::where('id_detalle_jornada', $id_detalle_jornada)->update(['activo' => 0])) {
                return response()->json([
                    'detalles' => 'Se ha cancelado tu participación a la jornada satisfactoriamente'
                ]);
            }
        }else {
            return response()->json([
                'detalles' => 'Este enlace ya no es válido'
            ], 400);
        }

    }


}
