<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Middleware\AttachJwtToken;

Route::get('/', function () {
    return view('index');
});

Route::get('/play', function () {
    return view('game');
});

Route::get('login', function () {
    return view('login');
})->name('login');

Route::get('register', function () {
    return view('register');
});

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::group(['middleware' => [AttachJwtToken::class, 'auth:api']], function() {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    // Route::get('/user', function() {
    //     return auth()->user();
    // });
    Route::get('/me', function() {
        return view('user');
    });
});