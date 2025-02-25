<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\Passport;

class AuthController extends Controller
{
    public function register(Request $request) {}

    // ----------------------------

    public function login(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'username' => 'required',
                'password' => 'required',
            ],
            ['*.required' => ':Attribute is required'],
            ['username' => 'username', 'password' => 'password']
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $credentials = $request->only('password');
        $field = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';
        $credentials[$field] = $request->username;

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();
        $token = $user->createToken('authToken')->accessToken;

        return UserResource::make($user)->additional(['token' => $token]);
    }

    // ----------------------------

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['message' => 'Successfully logged out']);
    }

    // ----------------------------

    public function me(Request $request)
    {
        return UserResource::make($request->user());
    }

    // ----------------------------

    public function refresh(Request $request) {}

    // ----------------------------

    public function forgotPassword(Request $request) {}

    // ----------------------------

    public function resetPassword(Request $request) {}
}
