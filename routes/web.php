<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Users\Create;
use App\Livewire\Users\Edit;
use App\Livewire\Users\Index;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    // Configuraciones del sistema - Solo administradores
    Route::middleware(['permission:administrar sistema'])->group(function () {
        Route::get('settings/configurations', \App\Livewire\Settings\Configurations::class)->name('settings.configurations');
        Route::get('settings/holidays', \App\Livewire\Settings\Holidays::class)->name('settings.holidays');
    });

    // Rutas para gestión de usuarios
    Route::middleware(['permission:ver usuarios'])->group(function () {
        Route::get('/users', Index::class)->name('users.index');
        Route::get('/users/create', Create::class)->middleware('permission:crear usuarios')->name('users.create');
        Route::get('/users/{user}/edit', Edit::class)->middleware('permission:editar usuarios')->name('users.edit');
    });

    // Rutas para gestión de clientes
    Route::middleware(['permission:ver clientes'])->group(function () {
        Route::get('/clients', \App\Livewire\Clients\Index::class)->name('clients.index');
        Route::get('/clients/create', \App\Livewire\Clients\Create::class)->middleware('permission:crear clientes')->name('clients.create');
        Route::get('/clients/{cliente}/edit', \App\Livewire\Clients\Edit::class)->middleware('permission:editar clientes')->name('clients.edit');
        Route::get('/clients/{cliente}', \App\Livewire\Clients\Show::class)->name('clients.show');
    });

    // Rutas para préstamos
    Route::middleware(['permission:ver prestamos'])->group(function () {
        Route::get('/prestamos/nuevo', \App\Livewire\Prestamos\Create::class)->name('prestamos.create');
        Route::get('/prestamos/autorizados', \App\Livewire\Prestamos\Autorizados::class)->name('prestamos.autorizados');
        // Permitir acceso a Administrador y Asesor
        Route::get('/prestamos/en-comite', \App\Livewire\Prestamos\EnComite::class)->middleware('role:Administrador|Asesor')->name('prestamos.en-comite');
        Route::get('/prestamos', \App\Livewire\Prestamos\Index::class)->name('prestamos.index');
        // Rutas para vistas imprimibles (detalle / pagaré / calendario / estado_cuenta)
        Route::get('/prestamos/{prestamo}/print/{type}', function (\App\Models\Prestamo $prestamo, $type) {
            if (! in_array($type, ['detalle', 'pagare', 'calendario', 'estado_cuenta'])) {
                abort(404);
            }

            if ($type === 'detalle') {
                return view('pdfs.prestamo_detalle', ['prestamo' => $prestamo]);
            }

            if ($type === 'calendario') {
                return view('pdfs.prestamo_calendario', ['prestamo' => $prestamo]);
            }

            if ($type === 'estado_cuenta') {
                return view('pdfs.prestamo_estado_cuenta', ['prestamo' => $prestamo]);
            }

            return view('pdfs.prestamo_pagare', ['prestamo' => $prestamo]);
        })->name('prestamos.print');

        // Ruta para descarga en PDF (usa barryvdh/laravel-dompdf si está instalado)
        Route::get('/prestamos/{prestamo}/print/{type}/download', function (\App\Models\Prestamo $prestamo, $type) {
            if (! in_array($type, ['detalle', 'pagare', 'calendario', 'estado_cuenta'])) {
                abort(404);
            }

            // Intentar generar PDF con la librería barryvdh/laravel-dompdf
            if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                $view = match ($type) {
                    'detalle' => 'pdfs.prestamo_detalle',
                    'calendario' => 'pdfs.prestamo_calendario',
                    'estado_cuenta' => 'pdfs.prestamo_estado_cuenta',
                    'pagare' => 'pdfs.prestamo_pagare',
                };
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($view, ['prestamo' => $prestamo, 'forPdf' => true])->setPaper('a4');
                $filename = "prestamo-{$prestamo->id}-{$type}.pdf";

                return $pdf->download($filename);
            }

            abort(501, 'La generación de PDF en servidor no está disponible. Instala barryvdh/laravel-dompdf: composer require barryvdh/laravel-dompdf');
        })->name('prestamos.print.download');

        Route::get('/prestamos/{id}', \App\Livewire\Prestamos\Show::class)->name('prestamos.show');
        Route::get('/prestamos/{prestamo}/editar', \App\Livewire\Prestamos\Edit::class)->middleware('permission:editar prestamos')->name('prestamos.edit');
    });

    // Rutas para pagos/cobros
    Route::middleware(['permission:ver prestamos'])->group(function () {
        Route::get('/pagos', \App\Livewire\Pagos\Index::class)->name('pagos.index');
        Route::get('/pagos/cobro-grupal/{prestamoId}', \App\Livewire\Pagos\CobroGrupal::class)->name('pagos.cobro-grupal');
        Route::get('/pagos/desglose-efectivo/{prestamoId}', \App\Livewire\Pagos\DesgloseEfectivo::class)->name('pagos.desglose-efectivo');
    });

    // Ruta para historial de clientes
    Route::middleware(['permission:ver clientes'])->group(function () {
        Route::get('/clientes/{id}/historial', function ($id) {
            return redirect()->route('clients.show', $id);
        })->name('clientes.historial');
    });

});

require __DIR__.'/auth.php';
