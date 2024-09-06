<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\RestaurantController as AdminRestaurantController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\TermController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserController;
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
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{id}', [AdminUserController::class, 'show'])->name('users.show');

    Route::resource('restaurants', AdminRestaurantController::class);
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);

    Route::resource('company', CompanyController::class)->only(['index', 'edit', 'update']);
    Route::resource('terms', TermController::class)->only(['index', 'edit', 'update']);
});

// 管理者以外
Route::group(['middleware' => 'guest:admin'], function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::resource('restaurants', RestaurantController::class)->only(['index', 'show']);
});

// 一般会員のみ
Route::group(['middleware' => ['guest:admin', 'auth', 'verified']], function () {
    Route::resource('user', UserController::class)->only(['index', 'edit', 'update']);

    Route::resource('restaurants.reviews', ReviewController::class)->only('index');

    // 有料プランに未登録
    Route::group(['middleware' => NotSubscribed::class], function () {
        Route::group(['prefix' => '/subscription', 'as' => 'subscription.'], function () {
            Route::get('/create', [SubscriptionController::class, 'create'])->name('create');
            Route::post('/', [SubscriptionController::class, 'store'])->name('store');
        });
    });

    // 有料プランに登録済み
    Route::group(['middleware' => Subscribed::class], function () {
        Route::group(['prefix' => '/subscription', 'as' => 'subscription.'], function () {
            Route::get('/edit', [SubscriptionController::class, 'edit'])->name('edit');
            Route::match(['put', 'patch'], '/', [SubscriptionController::class, 'update'])->name('update');
            Route::get('/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
            Route::delete('/', [SubscriptionController::class, 'destroy'])->name('destroy');
        });

        Route::resource('restaurants.reviews', ReviewController::class)->except(['index', 'show']);

        Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
        Route::get('/restaurants/{restaurant}/reservations/create', [ReservationController::class, 'create'])->name('restaurants.reservations.create');
        Route::post('/restaurants/{restaurant}/reservations', [ReservationController::class, 'store'])->name('restaurants.reservations.store');
        Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy'])->name('reservations.destroy');

        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
        Route::post('/favorites/{restaurant}', [FavoriteController::class, 'store'])->name('favorites.store');
        Route::delete('/favorites/{restaurant}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');
    });
});
