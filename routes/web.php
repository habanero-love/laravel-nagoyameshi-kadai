<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\TermController;
use App\Http\Controllers\HomeController;

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

require __DIR__ . '/auth.php';

Route::group(['prefix' => '/admin', 'as' => 'admin.', 'middleware' => 'auth:admin'], function () {
    Route::get('/home', [Admin\HomeController::class, 'index'])->name('home');
    Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/{id}', [Admin\UserController::class, 'show'])->name('users.show');

    Route::resource('restaurants', RestaurantController::class);
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);

    Route::resource('company', CompanyController::class)->only(['index', 'edit', 'update']);
    Route::resource('terms', TermController::class)->only(['index', 'edit', 'update']);
});

Route::group(['middleware' => 'guest:admin'], function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
});
