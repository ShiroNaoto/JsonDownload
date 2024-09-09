<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use App\Http\Resources\V1\UserResource;
use App\Http\Resources\V1\UserCollection;

use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = User::query();
        $users = new UserCollection($baseQuery->latest()->get());
        return response()->json($users);
    }

    public function show(User $user){
        return new UserResource($user);
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());
        $token = $user->createToken('AccessToken')->accessToken;
        return response()->json([
            'message' => 'User Created!',
            'name' => $user->name,
            'token' => $token,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $validatedData = $request->validated();
        if (isset($validatedData['password']) && $validatedData['password']) {
            $validatedData['password'] = bcrypt($validatedData['password']);
        } else {
            unset($validatedData['password']);
        }
        $user->update($validatedData);
        return response()->json("User Updated!");
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json("User Deleted!");
    }
}
