<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EmotionRegulationMechanism;
use App\Models\RegulationMechanism;
use App\Models\Client;
use App\Models\Emotion;
use App\Http\Resources\EmotionRegulationMechanism\EmotionRegulationMechanismResource;
use App\Http\Resources\EmotionRegulationMechanism\EmotionRegulationMechanismCollection;
use App\Http\Requests\EmotionRegulationMechanism\EmotionRegulationMechanismRequest;
use App\Http\Requests\EmotionExpression\UpdateEmotionExpressionRequest;
use Illuminate\Support\Facades\DB;

class EmotionRegulationMechanismController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return EmotionRegulationMechanismCollection
     */
    public function index()
    {
        $mechanisms = EmotionRegulationMechanism::where("client_id", Auth::user()->userable->id)->get();

        if(count($mechanisms) == 0){
            $mechanisms = EmotionRegulationMechanism::where("is_default", '1')->get();
        }
        return new EmotionRegulationMechanismCollection($mechanisms);
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
     * @param  EmotionRegulationMechanismRequest $emotionRegulationMechanismRequest
     * @return EmotionRegulationMechanismResource
     */
    public function store(EmotionRegulationMechanismRequest $emotionRegulationMechanismRequest)
    {
        $emotionRegulationMechanism = new EmotionRegulationMechanism();
        $validated_data = $emotionRegulationMechanismRequest->validated();

        try{
            DB::beginTransaction();
            $emotionRegulationMechanism->emotionToRegulate()->associate(Emotion::find($validated_data["emotion"]));
            $emotionRegulationMechanism->regulationMechanism()->associate(RegulationMechanism::find($validated_data["regulation_mechanism"]));
            $emotionRegulationMechanism->client()->associate(Auth::user()->userable);
            $emotionRegulationMechanism->is_default = false;
            $emotionRegulationMechanism->save();
            DB::commit();
            return new EmotionRegulationMechanismResource($emotionRegulationMechanism);
        }catch(\Throwable $th){
            DB::rollback();
            return response()->json(array(
                'code'      =>  400,
                'message'   =>  $th->getMessage()
            ), 400);
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  EmotionRegulationMechanism $emotionRegulationMechanism
     * @return EmotionRegulationMechanismResource
     */
    public function show($emotionRegulationMechanism)
    {
        $erm = EmotionRegulationMechanism::find($emotionRegulationMechanism);
        if($erm->is_default == false and $erm->client != Auth::user()->userable){
            return response()->json(array(
                'code'      =>  403,
                'message'   =>  "Resource not available to your account"
            ), 403);
        }
        return new EmotionRegulationMechanismResource($erm);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        abort(404);
    }

     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        abort(404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($emotionRegulationMechanism)
    {
        $erm = EmotionRegulationMechanism::find($emotionRegulationMechanism);
        if($erm->client == null || $erm->client != Auth::user()->userable){
            return response()->json(array(
                'code'      =>  403,
                'message'   =>  "Resource not available to your account"
            ), 403);
        }
        EmotionRegulationMechanism::destroy($emotionRegulationMechanism);
        return response()->json(array(
            'code'      =>  200,
            'message'   =>  "Emotion regulation mechanisms was successfully removed"
        ), 200);
    }
}
