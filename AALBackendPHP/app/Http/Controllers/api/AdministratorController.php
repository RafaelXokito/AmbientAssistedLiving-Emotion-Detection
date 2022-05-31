<?php

namespace App\Http\Controllers\api;

use App\Http\Requests\Administrator\CreateAdministratorRequest;
use App\Http\Requests\Administrator\UpdateAdministratorRequest;
use App\Http\Resources\Administrator\AdministratorCollection;
use App\Http\Resources\Administrator\AdministratorResource;
use App\Models\Administrator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdministratorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return AdministratorCollection
     */
    public function index()
    {
        return new AdministratorCollection(Administrator::all());
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
     * @return AdministratorResource|\Illuminate\Http\JsonResponse
     */
    public function store(CreateAdministratorRequest $request)
    {
        $admin = new Administrator();
        $validated_data = $request->validated();

        try {
            DB::beginTransaction();

            $admin->save();

            $admin->user()->create([
                'name' => $validated_data["name"],
                'email' => $validated_data["email"],
                'password' => bcrypt($validated_data["password"]),
            ]);

            $admin->save();
            //$admin->user->sendEmailVerificationNotification();
            DB::commit();

            return new AdministratorResource($admin);
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
     * @param  int  $administrator
     * @return AdministratorResource
     */
    public function show(Administrator $administrator)
    {
        return new AdministratorResource($administrator);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Administrator  $administrator
     * @return AdministratorResource
     */
    public function edit(Administrator $administrator)
    {
        return new AdministratorResource($administrator);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateAdministratorRequest $request
     * @param  Administrator  $administrator
     * @return AdministratorResource|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateAdministratorRequest $request,Administrator $administrator)
    {
        $validated_data = $request->validated();
        try {
            DB::beginTransaction();

            $administrator->user->name = $validated_data["name"];
            $administrator->user->email = $validated_data["email"];

            $administrator->save();

            DB::commit();

            return new AdministratorResource($administrator);
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
     * @param  Administrator  $administrator
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Administrator $administrator)
    {
        $oldName = $administrator->user->name;
        $oldEmail = $administrator->user->email;

        $administrator->user->delete();
        $administrator->delete();

        return response()->json(array(
            'code'      =>  200,
            'message'   =>  "Administrator was removed [". $oldEmail ."]:". $oldName ."!"
        ), 200);
    }
}
