<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CarterasController;
use App\Http\Controllers\ReportesController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ===================
// PÁGINAS PRINCIPALES
// ===================
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ===================
// AUTENTICACIÓN
// ===================
require __DIR__ . '/auth.php';

// ===================
// PERFIL (AUTENTICADO)
// ===================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ===================
// USUARIOS
// ===================
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');


// ===================
// CARTERAS
// ===================
Route::get('/carteras', [CarterasController::class, 'index'])->name('carteras.index');
Route::post('/carteras', [CarterasController::class, 'store'])->name('carteras.store');
Route::put('/carteras/{cartera}', [CarterasController::class, 'update'])->name('carteras.update');
Route::delete('/carteras/{cartera}', [CarterasController::class, 'destroy'])->name('carteras.destroy');


// ===================
// REPORTES
// ===================
Route::get('/reportes', [ReportesController::class, 'index'])->name('reportes.index');
Route::post('/reportes', [ReportesController::class, 'store'])->name('reportes.store');
Route::put('/reportes/{reporte}', [ReportesController::class, 'update'])->name('reportes.update');
Route::delete('/reportes/{reporte}', [ReportesController::class, 'destroy'])->name('reportes.destroy');

