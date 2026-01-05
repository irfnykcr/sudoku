<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller {
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'user' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if($validator->fails()){
            return back()->withErrors($validator)->withInput();
        }

        $difficulties = array_keys(config('sudoku.difficulties'));
        $stats = array_fill_keys($difficulties, 0);
        $stats['Daily'] = 0;

        $user = User::create([
            'user' => $request->get('user'),
            'password' => Hash::make($request->get('password')),
            'stats' => $stats,
        ]);

        $token = JWTAuth::fromUser($user);

        return redirect('/me')->withCookie(cookie('token', $token, 60));
    }

    public function login(Request $request) {
        $login = $request->input('login');
        $password = $request->input('password');

        $credentials = ['user' => $login, 'password' => $password];

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return back()->withErrors(['login' => 'Invalid credentials']);
            }
        } catch (JWTException $e) {
            return back()->withErrors(['login' => 'Could not create token']);
        }

        return redirect('/me')->withCookie(cookie('token', $token, 60));
    }

    public function logout() {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (\Exception $e) {
            // token might be already invalid
        }
        return redirect('/login')->withCookie(cookie()->forget('token'));
    }

    public function refresh() {
        try {
            $token = JWTAuth::parseToken()->refresh();
            return response()->json(['success' => true])
                ->withCookie(cookie('token', $token, 60));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
}
