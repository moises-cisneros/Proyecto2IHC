<?php

use App\Http\Controllers\AutentificacionController;
use App\Http\Controllers\ColaboracionController;
use App\Http\Controllers\HistorialController;
use App\Http\Controllers\PlanesController;
use Illuminate\Support\Facades\Route;



Route::redirect('/', '/ingresar');

Route::get( '/ingresar', [AutentificacionController::class, 'mostrarIngreso'])->name('login');
Route::post('/ingresar', [AutentificacionController::class, 'ingresar']       )->name('ingresar');

Route::get( '/registro', [AutentificacionController::class, 'mostrarRegistro'])->name('registro');
Route::post('/registro', [AutentificacionController::class, 'registrar']       )->name('registrar');

Route::get( '/recuperar', [AutentificacionController::class, 'mostrarRecuperar'])->name('recuperar');
Route::post('/recuperar', [AutentificacionController::class, 'enviarCorreo']     )->name('enviar.correo');

Route::get( '/restablecer/{token}', [AutentificacionController::class, 'mostrarRestablecer'])->name('password.reset');
Route::post('/restablecer',         [AutentificacionController::class, 'restablecer']        )->name('restablecer');


Route::middleware('auth')->group(function () {

    Route::post('/salir', [AutentificacionController::class, 'salir'])->name('salir');

    Route::get(   '/mis-planes',             [PlanesController::class, 'listar']       )->name('planes.listar');
    Route::post(  '/planes',                 [PlanesController::class, 'guardar']      )->name('planes.guardar');
    Route::get(   '/planes/{plan}',          [ColaboracionController::class, 'detalle'])->name('planes.detalle');
    Route::get(   '/planes/{plan}/editar',   [PlanesController::class, 'editar']       )->name('planes.editar');
    Route::put(   '/planes/{plan}',          [PlanesController::class, 'actualizar']   )->name('planes.actualizar');
    Route::delete('/planes/{plan}',          [PlanesController::class, 'eliminar']     )->name('planes.eliminar');
    Route::put(   '/planes/{plan}/estado',   [PlanesController::class, 'cambiarEstado'])->name('planes.estado');

    Route::post(  '/planes/{plan}/invitar',  [ColaboracionController::class, 'invitar']      )->name('planes.invitar');
    Route::post(  '/planes/{plan}/opciones', [ColaboracionController::class, 'guardarOpcion'])->name('planes.opciones.guardar');
    Route::post(  '/opciones/{opcion}/votar', [ColaboracionController::class, 'votar']       )->name('opciones.votar');
    Route::post(  '/planes/{plan}/abandonar', [ColaboracionController::class, 'abandonar']   )->name('planes.abandonar');

    Route::get(   '/historial',              [HistorialController::class, 'index']     )->name('historial.index');

});
