<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Passport;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials)) {

            $user = Auth::user();
            $token = $user->createToken('AccessToken')->accessToken;
            
            return response()->json([
                'status' => true,
                'message' => 'Login Successful!',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->acctype,
                ],
                'token' => $token,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Login Failed. Please request for Authorization to access the dashboard'
            ], 401);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->tokens->each(function ($token) {
                $token->update(['revoked' => true]);
            });
            return response()->json(['message' => 'Logout Successful!'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Logout Failed. Please try again.'], 500);
        }
    }

}
