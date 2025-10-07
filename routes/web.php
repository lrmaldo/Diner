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
        Route::get('/prestamos', \App\Livewire\Prestamos\Index::class)->name('prestamos.index');
        Route::get('/prestamos/{id}', \App\Livewire\Prestamos\Show::class)->name('prestamos.show');
        Route::get('/prestamos/{prestamo}/editar', \App\Livewire\Prestamos\Edit::class)->middleware('permission:editar prestamos')->name('prestamos.edit');
    });

    // Ruta para historial de clientes
    Route::middleware(['permission:ver clientes'])->group(function () {
        Route::get('/clientes/{id}/historial', function ($id) {
            return redirect()->route('clients.show', $id);
        })->name('clientes.historial');
    });

});



require __DIR__.'/auth.php';
