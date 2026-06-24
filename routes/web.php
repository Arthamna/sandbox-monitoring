<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    // return Inertia::render('auth/register');
    return Inertia::render('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('login', function () {
        return Inertia::render('auth/login');
    })->name('login');

    Route::get('register', function () {
        return Inertia::render('auth/register');
    })->name('register');
});

Route::get('dashboard', function () {
    return Inertia::render('dashboard');
})->name('dashboard');

Route::get('admin/sandboxes', function () {
    $users = User::whereNot('role', 'admin')
        ->select('id', 'username')
        ->get();

    return Inertia::render('admin/sandboxes', [
        'users' => $users  
    ]);

})->name('admin.sandboxes');

Route::get('admin/logs', function () {
    return Inertia::render('admin/logs');
})->name('admin.logs');

// require __DIR__.'/settings.php';
// require __DIR__.'/auth.php';
