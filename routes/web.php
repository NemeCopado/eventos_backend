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

// RUTAS DE USUARIOS
Route::group(['middeware'=>[], 'prefix'=>'usuarios'], function(){
    //Login de un usuario
    Route::get('/login', 'App\Http\Controllers\UsuariosController@login');
    //Listado de usuarios
    Route::get('/', 'App\Http\Controllers\UsuariosController@index');
    //Listado de un solo usuario
    Route::get('/{id}', 'App\Http\Controllers\UsuariosController@show');
    //Actualización de un usuario
    Route::put('/update/{id}', 'App\Http\Controllers\UsuariosController@update');
});

// RUTAS DE INSTITUCIONES
Route::group(['middeware'=>[], 'prefix'=>'instituciones'], function(){
    //Listado de instituciones
    Route::get('/', 'App\Http\Controllers\InstitucionesController@index');
    //Listado una sola institución
    Route::get('/{id}', 'App\Http\Controllers\InstitucionesController@show');
    //Alta de institución con su enlace
    Route::post('/store', 'App\Http\Controllers\InstitucionesController@store');
    //Eliminación de institución con su enlace
    Route::delete('/destroy/{id}', 'App\Http\Controllers\InstitucionesController@destroy');
});

// RUTAS DE VOLUNTARIOS
Route::group(['middeware'=>[], 'prefix'=>'voluntarios'], function(){
    //Listado de voluntarios
    Route::get('/', 'App\Http\Controllers\VoluntariosController@index');
    //Descargar Reporte de voluntarios
    Route::get('/reporte', 'App\Http\Controllers\VoluntariosController@reporte');
    //Eliminación de un voluntario
    Route::delete('/destroy/{id_voluntario}', 'App\Http\Controllers\VoluntariosController@destroy');
    //Listar registro de todos los voluntarios por institución
    Route::get('/institucion/{id_institucion}', 'App\Http\Controllers\VoluntariosController@show');
    //Registro de voluntarios
    Route::post('/store', 'App\Http\Controllers\VoluntariosController@store');
    //Asignación de sedes a los voluntarios
    Route::post('/asignarSede', 'App\Http\Controllers\VoluntariosController@asignarSede');
});

//RUTAS DE SEDES
Route::group(['middeware'=>[], 'prefix'=>'sedes'], function(){
    //Listado de sedes
    Route::get('/', 'App\Http\Controllers\SedesController@index');
    //Alta de sedes
    Route::post('/store', 'App\Http\Controllers\SedesController@store');
    //Eliminación de una sede
    Route::delete('/destroy/{id_sede}', 'App\Http\Controllers\SedesController@destroy');
    //Actualización de una sede
    Route::put('/update/{id_sede}', 'App\Http\Controllers\SedesController@update');
});
