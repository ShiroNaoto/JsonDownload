<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Response;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\V1\KeyResource;
use App\Http\Resources\V1\KeyCollection;

use App\Models\Key;

class KeyController extends Controller
{
    public function getAllRedisKeys()
    {
        $keysFromDB = Key::latest()->get();

        $data = [];

        foreach ($keysFromDB as $keyModel) {
            $redisKey = $keyModel->json_keys;
            $value = Redis::lrange($redisKey, 0, -1);
            
            $decodedValue = array_map(function ($value) {
                return json_decode($value, true);
            }, $value);

  
            $data[] = [
                'key' => $redisKey,
                'json_id' => $keyModel->json_id,     
                'contains' => $keyModel->contains, 
                'date' => $keyModel->created_at->format('F j, Y g:i A'),
                'value' => $decodedValue !== null ? $decodedValue : $value,
            ];
        }

        return response()->json($data);
    }

    public function storeJson(Request $request)
    {
        $data = $request->json()->all();
        $dateKey = 'json:' . now()->format('Y-m-d');
        $jsonPayload = json_encode($data);
        $raw_content = file_get_contents('php://input');
        $json_request = json_decode(stripslashes($raw_content), true);
        
        //Stores Save Termrates
        if ($json_request['save_termrates'] ?? false) {
            Redis::rpush($dateKey, $jsonPayload);
            $storedValues = Redis::lrange($dateKey, 0, -1);
            $decodedValues = array_map(function ($value) {
                return json_decode($value, true);
            }, $storedValues);
        
            foreach ($json_request['save_termrates'] as $termrate) {
                if (isset($termrate['levelid'])) {
                    Key::create([
                        'json_keys' => $dateKey,
                        'json_id' => $termrate['levelid'],
                        'contains' => 'termrates'
                    ]);
                }
            }
            \Log::info('Stored JSON in Redis:', [$dateKey => $decodedValues]);

        } else if ($json_request['delete_soa'] ?? false) {
            Redis::rpush($dateKey, $jsonPayload);
            $storedValues = Redis::lrange($dateKey, 0, -1);
            $decodedValues = array_map(function ($value) {
                return json_decode($value, true);
            }, $storedValues);

            foreach ($json_request['delete_soa'] as $studId) {
                if (isset($studId['student_id'])) {
                    Key::create([
                        'json_keys' => $dateKey,
                        'json_id' => $studId['student_id'],
                        'contains' => 'soa'
                    ]);
                }
            }
            \Log::info('Stored JSON in Redis:', [$dateKey => $decodedValues]);

        } else {
            return response()->json([
                'message' => 'No valid action specified.',
            ], 400);
        }

        return response()->json([
            'message' => 'JSON stored successfully',
            'redis_key' => $dateKey,
        ]);
    }

    public function downloadTermRate($key)
    {
        $redisKey = Key::where('json_keys', $key)->first();

        if ($redisKey) {
            $values = Redis::lrange($redisKey->json_keys, 0, -1);

            $filteredValues = array_filter($values, function ($value) {
                $decodedValue = json_decode($value, true);
                return ($decodedValue['save_termrates'] ?? false) 
                && !($decodedValue['delete_soa'] ?? false);
            });

            $decodedFilteredValues = array_map(function ($value) {
                return json_decode($value, true);
            }, $filteredValues);

            $fileName = $key . '_filtered.json';
            $jsonContent = json_encode($decodedFilteredValues, JSON_PRETTY_PRINT);

            return response()->streamDownload(function() use ($jsonContent) {
                echo $jsonContent;
            }, $fileName, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            ]);
        } else {
            return response()->json(['error' => 'Key not found'], 404);
        }
    }

    public function downloadSOA($key)
    {
        $redisKey = Key::where('json_keys', $key)->first();

        if ($redisKey) {
            $values = Redis::lrange($redisKey->json_keys, 0, -1);

            $filteredValues = array_filter($values, function ($value) {
                $decodedValue = json_decode($value, true);
                return ($decodedValue['delete_soa'] ?? false)
                && !($decodedValue['save_termrates'] ?? false);
            });

            $decodedFilteredValues = array_map(function ($value) {
                return json_decode($value, true);
            }, $filteredValues);

            $fileName = $key . '_filtered_soa.json';
            $jsonContent = json_encode($decodedFilteredValues, JSON_PRETTY_PRINT);

            return response()->streamDownload(function() use ($jsonContent) {
                echo $jsonContent;
            }, $fileName, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            ]);
        } else {
            return response()->json(['error' => 'Key not found'], 404);
        }
    }
}
