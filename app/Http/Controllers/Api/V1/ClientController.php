<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Passport\ClientRepository;

class ClientController extends Controller
{
    public function createClient(Request $request)
    {
        $clientRepository = new ClientRepository();
        $client = $clientRepository->create(null,
            $request->name,
            $request->redirect_uri,
        );

        return response()->json([
            'client_id' => $client->id,
            'client_secret' => $client->secret,
        ]);
    }

    public function deleteClient($id)
    {
        $client = \Laravel\Passport\Client::find($id);

        if ($client) {
            $client->delete();
            return response()->json("Client Deleted!");
            }

        return response()->json("Client not found!", 404);
    }

    public function updateClient(Request $request, $clientId)
    {
        $clientRepository = new ClientRepository();
        $client = $clientRepository->find($clientId);

        $client->update([
            'name' => $request->name,
            'redirect' => $request->redirect_uri,
        ]);

        return response()->json("Client Updated!");
    }

}
