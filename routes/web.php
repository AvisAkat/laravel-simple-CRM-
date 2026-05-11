<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest'])->controller(AuthController::class)->name('auth.')->group(function () {
    Route::get('/', 'showLoginForm')->name('loginForm');
    Route::post('/login', 'loginHandler')->name('login');
});

Route::middleware('auth')->name('admin.')->prefix('admin')->controller(AdminController::class)->group(function () {
    Route::post('/logout', 'logoutHandler')->name('logout');
    Route::get('/dashboard', 'dashboard')->name('dashboard');
    Route::get('/customers', 'showCustomers')->name('customers');
    Route::get('/leads', 'showLeads')->name('leads');
    Route::get('/tasks', 'showTasks')->name('tasks');
    Route::get('/profile', 'showProfile')->name('profile');
    Route::get('/users', 'showUsers')->name('allUsers');
    Route::get('/add-users', 'showAddUserForm')->name('addUserForm');
    Route::post('/add-user', 'addUser')->name('addUser');
    Route::get('/edit-user/{id}', 'editUser')->name('editUser');
    Route::post('/update-user', 'updateUser')->name('updateUser');
});


