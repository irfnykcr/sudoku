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

use App\Http\Controllers\GameController;

Route::middleware([AttachJwtToken::class])->group(function () {
    Route::post('refresh', [AuthController::class, 'refresh']);
});

Route::group(['middleware' => [AttachJwtToken::class, 'auth:api']], function() {
    Route::post('logout', [AuthController::class, 'logout']);
    // Route::get('/user', function() {
    //     return auth()->user();
    // });
    Route::get('/me', function() {
        $user = auth()->user();
        $username = $user ? $user->user : 'Guest';
        return view('user', ['username' => $username]);
    });

    // game Routes
    Route::get('/api/game/load', [GameController::class, 'load']);
    Route::post('/api/game/start', [GameController::class, 'start']);
    Route::post('/api/game/save', [GameController::class, 'save']);
    Route::post('/api/game/reset', [GameController::class, 'reset']);
    Route::post('/api/game/check', [GameController::class, 'check']);
});