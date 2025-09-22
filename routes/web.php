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
});

require __DIR__.'/auth.php';
