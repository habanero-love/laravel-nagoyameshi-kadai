<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\RestaurantController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

require __DIR__.'/auth.php';

Route::group(['prefix' => '/admin', 'as' => 'admin.', 'name'=>'admin.', 'middleware' => 'auth:admin'], function () {
    Route::get('/home', [Admin\HomeController::class, 'index'])->name('home');
    Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/{id}', [Admin\UserController::class, 'show'])->name('users.show');

    Route::resource('restaurants', RestaurantController::class);
});