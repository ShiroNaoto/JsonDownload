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

    public function getAllRedisKeys()
    {
        $keys = Redis::keys('*');
        $data = [];
    
        foreach ($keys as $key) {
            $value = Redis::lrange($key, 0, -1);
            $decodedValue = array_map(function ($value) {
                return json_decode($value, true);
            },$value);
    
            $data[] = [
                'key' => $key,
                'value' => $decodedValue !== null ? $decodedValue : $value,
            ];
        }
        return response()->json($data);
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

    public function storeJson(Request $request)
    {
        $data = $request->json()->all();
        $dateKey = 'json:' . now()->format('Y-m-d');
        $jsonPayload = json_encode($data);
        Redis::lpush($dateKey, $jsonPayload);

        $storedValues = Redis::lrange($dateKey, 0, -1);
        $decodedValues = array_map(function ($value) {
            return json_decode($value, true);
        }, $storedValues);

        \Log::info('Stored JSON in Redis:', [$dateKey => $decodedValues]);

        return response()->json([
            'message' => 'JSON stored successfully',
            'redis_key' => $dateKey,
            'data' => $decodedValues,
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

    public function downloadRedisKey($key)
    {
        $value = Redis::lrange($key, 0, -1);
        $decodedValue = array_map(function ($value) {
            return json_decode($value, true);
        }, $value);

        $data = [
            'key' => $key,
            'value' => $decodedValue !== null ? $decodedValue : $value,
        ];

        $fileName = $key . '.json';
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT);

        return response()->streamDownload(function() use ($jsonContent) {
            echo $jsonContent;
        }, $fileName, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }
}
