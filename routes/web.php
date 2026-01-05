<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\AttachJwtToken;
use App\Http\Controllers\GameController;

Route::middleware([AttachJwtToken::class])->group(function () {
    Route::get('/', function () {
        return view('index');
    });

    Route::get('/play', function () {
        return view('game', ['sudokuConfig' => config('sudoku')]);
    });

    Route::get('login', function () {
        if (auth('api')->check()) {
            return redirect('/me');
        }
        return view('login');
    })->name('login');

    Route::get('register', function () {
        if (auth('api')->check()) {
            return redirect('/me');
        }
        return view('register');
    });

    Route::post('refresh', [AuthController::class, 'refresh']);
    
    // public/guest game routes
    Route::post('/api/game/start', [GameController::class, 'start']);
    Route::post('/api/game/check', [GameController::class, 'check']);
    Route::get('/api/game/load', [GameController::class, 'load']);
    Route::post('/api/game/save', [GameController::class, 'save']);
    Route::post('/api/game/reset', [GameController::class, 'reset']);
});

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('logout', function() {
    return redirect('/login');
});

Route::group(['middleware' => [AttachJwtToken::class, 'auth:api']], function() {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('/me', function() {
        $user = auth()->user();
        $username = $user ? $user->user : 'Guest';
        return view('user', ['username' => $username]);
    });

    // game routes
    Route::get('/api/game/daily', [GameController::class, 'daily']);
    Route::get('/api/game/calendar', [GameController::class, 'calendar']);
    Route::get('/api/user/stats', [GameController::class, 'stats']);
});