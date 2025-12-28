<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:users',
            'email' => 'nullable|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);

        $token = JWTAuth::fromUser($user);

        return redirect('/me')->withCookie(cookie('token', $token, 60));
    }

    public function login(Request $request)
    {
        $login = $request->input('login');
        $password = $request->input('password');

        $loginField = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
        $credentials = [$loginField => $login, 'password' => $password];

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return back()->withErrors(['login' => 'Invalid credentials']);
            }
        } catch (JWTException $e) {
            return back()->withErrors(['login' => 'Could not create token']);
        }

        return redirect('/me')->withCookie(cookie('token', $token, 60));
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (\Exception $e) {
            // Token might be already invalid
        }
        return redirect('/login')->withCookie(cookie()->forget('token'));
    }

    public function refresh()
    {
        $token = JWTAuth::refresh(JWTAuth::getToken());
        return redirect('/me')->withCookie(cookie('token', $token, 60));
    }
}
