<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;
use App\Http\Controllers;
use App\Http\Middleware\NotSubscribed;
use App\Http\Middleware\Subscribed;

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
    // return view('welcome');
    return redirect('/home');
});

require __DIR__ . '/auth.php';

// 管理者のみ
Route::group(['prefix' => '/admin', 'as' => 'admin.', 'middleware' => 'auth:admin'], function () {
    Route::get('/home', [Admin\HomeController::class, 'index'])->name('home');
    Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/{id}', [Admin\UserController::class, 'show'])->name('users.show');

    Route::resource('restaurants', Admin\RestaurantController::class);
    Route::resource('categories', Admin\CategoryController::class)->except(['create', 'show', 'edit']);

    Route::resource('company', Admin\CompanyController::class)->only(['index', 'edit', 'update']);
    Route::resource('terms', Admin\TermController::class)->only(['index', 'edit', 'update']);
});

// 管理者以外
Route::group(['middleware' => 'guest:admin'], function () {
    Route::get('/home', [Controllers\HomeController::class, 'index'])->name('home');
    Route::resource('restaurants', Controllers\RestaurantController::class)->only(['index', 'show']);

    Route::get('/company', [Controllers\CompanyController::class, 'index'])->name('company.index');
    Route::get('/terms', [Controllers\TermController::class, 'index'])->name('terms.index');
});

// 一般会員のみ
Route::group(['middleware' => ['guest:admin', 'auth', 'verified']], function () {
    Route::resource('user', Controllers\UserController::class)->only(['index', 'edit', 'update']);

    Route::resource('restaurants.reviews', Controllers\ReviewController::class)->only('index');

    // 有料プランに未登録
    Route::group(['middleware' => NotSubscribed::class], function () {
        Route::group(['prefix' => '/subscription', 'as' => 'subscription.'], function () {
            Route::get('/create', [Controllers\SubscriptionController::class, 'create'])->name('create');
            Route::post('/', [Controllers\SubscriptionController::class, 'store'])->name('store');
        });
    });

    // 有料プランに登録済み
    Route::group(['middleware' => Subscribed::class], function () {
        Route::group(['prefix' => '/subscription', 'as' => 'subscription.'], function () {
            Route::get('/edit', [Controllers\SubscriptionController::class, 'edit'])->name('edit');
            Route::match(['put', 'patch'], '/', [Controllers\SubscriptionController::class, 'update'])->name('update');
            Route::get('/cancel', [Controllers\SubscriptionController::class, 'cancel'])->name('cancel');
            Route::delete('/', [Controllers\SubscriptionController::class, 'destroy'])->name('destroy');
        });

        Route::resource('restaurants.reviews', Controllers\ReviewController::class)->except(['index', 'show']);

        Route::get('/reservations', [Controllers\ReservationController::class, 'index'])->name('reservations.index');
        Route::get('/restaurants/{restaurant}/reservations/create', [Controllers\ReservationController::class, 'create'])->name('restaurants.reservations.create');
        Route::post('/restaurants/{restaurant}/reservations', [Controllers\ReservationController::class, 'store'])->name('restaurants.reservations.store');
        Route::delete('/reservations/{reservation}', [Controllers\ReservationController::class, 'destroy'])->name('reservations.destroy');

        Route::get('/favorites', [Controllers\FavoriteController::class, 'index'])->name('favorites.index');
        Route::post('/favorites/{restaurant}', [Controllers\FavoriteController::class, 'store'])->name('favorites.store');
        Route::delete('/favorites/{restaurant}', [Controllers\FavoriteController::class, 'destroy'])->name('favorites.destroy');
    });
});
