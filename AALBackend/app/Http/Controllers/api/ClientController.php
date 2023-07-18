<?php

namespace App\Http\Controllers\api;

use App\Http\Requests\Client\CreateClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Http\Resources\Client\ClientCollection;
use App\Http\Resources\Client\ClientResource;
use App\Models\Administrator;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ClientCollection
     */
    public function index()
    {
        return new ClientCollection(Client::orderBy('created_at', 'DESC')->get());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return ClientResource|\Illuminate\Http\JsonResponse
     */
    public function store(CreateClientRequest $request)
    {
        $client = new Client();
        $validated_data = $request->validated();

        try {
            DB::beginTransaction();

            $client->contact = $validated_data["contact"];
            $client->birthdate = $validated_data["birthdate"];
            $client->administrator()->associate(Auth::user()->userable);

            $client->save();

            $client->user()->create([
                'name' => $validated_data["name"],
                'email' => $validated_data["email"],
                'password' => bcrypt($validated_data["password"]),
            ]);

            $client->save();
            //$client->user->sendEmailVerificationNotification();
            DB::commit();

            return new ClientResource($client);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(array(
                'code'      =>  400,
                'message'   =>  $th->getMessage()
            ), 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $Client
     * @return ClientResource
     */
    public function show(Client $Client)
    {
        return new ClientResource($Client);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Client  $Client
     * @return ClientResource
     */
    public function edit(Client $Client)
    {
        return new ClientResource($Client);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateClientRequest $request
     * @param  Client  $Client
     * @return ClientResource|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateClientRequest $request,Client $Client)
    {
        $validated_data = $request->validated();
        try {
            DB::beginTransaction();

            $Client->user->name = $validated_data["name"];
            $Client->user->email = $validated_data["email"];

            $Client->save();

            DB::commit();

            return new ClientResource($Client);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json(array(
                'code'      =>  400,
                'message'   =>  $th->getMessage()
            ), 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Client  $Client
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Client $Client)
    {
        $oldName = $Client->user->name;
        $oldEmail = $Client->user->email;

        $Client->user->delete();
        $Client->delete();

        return response()->json(array(
            'code'      =>  200,
            'message'   =>  "Client was removed [". $oldEmail ."]:". $oldName ."!"
        ), 200);
    }

    public function getMe(){
        $client = Client::findOrFail(Auth::user()->userable->id);
        return new ClientResource($client);
    }

}
