<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

// RUTAS DE USUARIOS
//Listado de usuarios
Route::get('/usuarios', 'App\Http\Controllers\UsuariosController@index');
//Listado de un solo usuario
Route::get('/usuarios/{id}', 'App\Http\Controllers\UsuariosController@show');
//Actualización de un usuario
Route::put('/usuarios/update/{id}', 'App\Http\Controllers\UsuariosController@update');


// RUTAS DE INSTITUCIONES
//Listado de instituciones
Route::get('/instituciones', 'App\Http\Controllers\InstitucionesController@index');
//Listado una sola institución
Route::get('/instituciones/{id}', 'App\Http\Controllers\InstitucionesController@show');
//Alta de institución con su enlace
Route::post('/instituciones/store', 'App\Http\Controllers\InstitucionesController@store');
//Eliminación de institución con su enlace
Route::delete('/instituciones/destroy/{id}', 'App\Http\Controllers\InstitucionesController@destroy');

// RUTAS DE VOLUNTARIOS
//Listado de voluntarios
Route::get('/voluntarios', 'App\Http\Controllers\VoluntariosController@index');
//Descargar Reporte de voluntarios
Route::get('/voluntarios/reporte', 'App\Http\Controllers\VoluntariosController@reporte');
//Eliminación de un voluntario
Route::delete('/voluntarios/destroy/{id_voluntario}', 'App\Http\Controllers\VoluntariosController@destroy');
//Listar registro de todos los voluntarios por institución
Route::get('/voluntarios/institucion/{id_institucion}', 'App\Http\Controllers\VoluntariosController@show');
//Registro de voluntarios
Route::post('/voluntarios/store', 'App\Http\Controllers\VoluntariosController@store');
//Asignación de sedes a los voluntarios
Route::post('/voluntarios/asignarSede', 'App\Http\Controllers\VoluntariosController@asignarSede');

//RUTAS DE SEDES
//Listado de sedes
Route::get('/sedes', 'App\Http\Controllers\SedesController@index');
//Alta de sedes
Route::post('/sedes/store', 'App\Http\Controllers\SedesController@store');
//Eliminación de una sede
Route::delete('/sedes/destroy/{id_sede}', 'App\Http\Controllers\SedesController@destroy');
//Actualización de una sede
Route::put('/sedes/update/{id_sede}', 'App\Http\Controllers\SedesController@update');
