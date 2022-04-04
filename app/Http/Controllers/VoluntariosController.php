<?php

namespace App\Http\Controllers;
use App\Models\Detalle_Jornadas;
use App\Models\Sedes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Voluntarios;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class VoluntariosController extends Controller
{

    public function __construct()
    {
        $this->middleware('jwt');
    }

    //Listado de voluntarios
    public function index(){

        //Select de los voluntarios
        if ($voluntarios = DB::table('usuarios')
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
            ->get()){

            return response()->json([
                'total_registros'=>count($voluntarios),
                'detalles'=>$voluntarios
            ]);

        }else{

            return response()->json([
                'detalles'=>'Error al listar los voluntarios'
            ], 400);

        }

    }

    //Eliminación de un voluntario
    public function destroy($id_voluntario){

        //Update de activo y eliminado
        if (DB::table('voluntarios')->where('id_voluntario', $id_voluntario)->update(['activo'=>0, 'eliminado'=>1])){

            return response()->json([
                'detalles'=>'Se eliminó al voluntario satisfactoriamente'
            ]);

        }else{

            return response()->json([
                'detalles'=>'Error al eliminar al voluntario'
            ], 400);

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

            //Buscamos a todos los voluntarios pertenecientes a la institución solicitados
            //Retornamos los datos arrojados
            if ($voluntarios = DB::table('voluntarios')
                ->where('id_insti', $id_institucion)
                ->where('activo', 1)
                ->get()){

                return response()->json([
                    'detalles'=>$voluntarios
                ]);

            }

        //En caso de no existir la institución solicitada mostramos mensaje
        }else{

            return response()->json([
                'detalles'=>'No hay ninguna institución registrada con esa Id'
            ], 400);

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

                return response()->json([
                    'detalles'=>$errors
                ], 400);

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

                    return response()->json([
                        "detalles" => "Se registro el voluntario satisfactoriamente "."con Id: ".$voluntario->id,
                    ]);

                }else{

                    return response()->json([
                        'detalles'=>'Ocurrió un error al registrar al voluntario'
                    ], 400);

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

                return response()->json([
                    'detalles'=>$validator->errors()
                ], 400);

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

                        return response()->json([
                            "detalles" => 'Se ha hecho la asignación al voluntario exitosamente.'
                        ]);

                    }

                //Retornamos error de municipios distintos
                }else{

                    return response()->json([
                        "detalles" => 'El municipio de la sede no coincide con el municipio del voluntario registrado'
                    ], 400);

                }

            }

        }

    }

    //Reporte de voluntarios
    public function reporte(){

        //Select de la tabla voluntarios
        $voluntarios = Voluntarios::all();
        //Creamos objeto de hoja de calculo
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'id_voluntario');
        $sheet->setCellValue('B1', 'id_insti');
        $sheet->setCellValue('C1', 'id_municipio');
        $sheet->setCellValue('D1', 'nombre');
        $sheet->setCellValue('E1', 'ape_pat');
        $sheet->setCellValue('F1', 'ape_mat');
        $sheet->setCellValue('G1', 'curp');
        $sheet->setCellValue('H1', 'fecha_nacimiento');
        $sheet->setCellValue('I1', 'tel');
        $sheet->setCellValue('J1', 'email');
        $sheet->setCellValue('K1', 'activo');
        $rows = 2;
        foreach ($voluntarios as $one){
            $sheet->setCellValue('A' . $rows, $one['id_voluntario']);
            $sheet->setCellValue('B' . $rows, $one['id_insti']);
            $sheet->setCellValue('C' . $rows, $one['id_municipio']);
            $sheet->setCellValue('D' . $rows, $one['nombre']);
            $sheet->setCellValue('E' . $rows, $one['ape_pat']);
            $sheet->setCellValue('F' . $rows, $one['ape_mat']);
            $sheet->setCellValue('G' . $rows, $one['curp']);
            $sheet->setCellValue('H' . $rows, $one['fecha_nacimiento']);
            $sheet->setCellValue('I' . $rows, $one['tel']);
            $sheet->setCellValue('J' . $rows, $one['email']);
            $sheet->setCellValue('K' . $rows, $one['activo']);
            $rows++;
        }

        $fileName = 'voluntarios_'.date('d-m-Y').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        $writer->save('php://output');

    }


}
