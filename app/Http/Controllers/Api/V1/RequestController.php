<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;
use Laravel\Passport\Token;

class RequestController extends Controller
{
    public function createAccess(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $token = $user->createToken('AccessToken')->accessToken;

        return response()->json([
            'status' => true,
            'message' => 'Access token assigned successfully!'
        ], 200);
    }

    public function revokeAccess(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->tokens->each(function ($token) {
            $token->revoke();
        });

        return response()->json([
            'status' => true,
            'message' => 'Access tokens revoked successfully!',
        ], 200);
    }
}
