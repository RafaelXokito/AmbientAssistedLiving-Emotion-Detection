<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\RegulationMechanism;
use App\Http\Resources\RegulationMechanism\RegulationMechanismResource;
use App\Http\Resources\RegulationMechanism\RegulationMechanismCollection;
use App\Http\Requests\RegulationMechanism\RegulationMechanismRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RegulationMechanismController extends Controller
{
    public function index()
    {
        $mechanisms = RegulationMechanism::all();
        return new RegulationMechanismCollection($mechanisms);
    }

    public function show(RegulationMechanism $regulationMechanism)
    {
        return new RegulationMechanismResource($regulationMechanism);
    }

    public function create()
    {
        abort(404);
    }

    public function store(RegulationMechanismRequest $request)
    {
        try{
            DB::beginTransaction();
            $validated_data = $request->validated();
            $regulationMechanism = new RegulationMechanism();
            $regulationMechanism->description = $validated_data["description"];
            $regulationMechanism->client()->associate(Auth::user()->userable);
            $regulationMechanism->save();
            DB::commit();
            return new RegulationMechanismResource($regulationMechanism);
        }
        catch (\Throwable $th) {
            DB::rollBack();

            return response()->json(array(
                'code'      =>  400,
                'message'   =>  $th->getMessage()
            ), 400);
        }

    }

    public function edit($id)
    {
        abort(404);
    }

    public function update(RegulationMechanismRequest $request, RegulationMechanism $regulationMechanism)
    {
        try{
            DB::beginTransaction();
            $validated_data = $request->validated();
            $regulationMechanism->description = $validated_data["description"];
            $regulationMechanism->save();
            DB::commit();
            return new RegulationMechanismResource($regulationMechanism);
        }
        catch (\Throwable $th) {
            DB::rollBack();

            return response()->json(array(
                'code'      =>  400,
                'message'   =>  $th->getMessage()
            ), 400);
        }
    }


    public function destroy(RegulationMechanism $regulationMechanism)
    {
        $regulationMechanism->delete();

        return response()->json(array(
            'code'      =>  200,
            'message'   =>  "Regulation mechanism was deleted"
        ), 200);
    }
}
