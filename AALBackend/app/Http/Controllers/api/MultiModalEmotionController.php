<?php

namespace App\Http\Controllers\api;

use App\Models\Emotion;
use Illuminate\Http\Request;
use App\Models\MultiModalEmotion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\MultiModalEmotion\MultiModalEmotionResource;
use App\Http\Resources\MultiModalEmotion\MultiModalEmotionCollection;
use App\Http\Requests\MultiModalEmotion\CreateMultiModalEmotionRequest;

class MultiModalEmotionController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return MultiModalEmotionCollection
     */
    public function index()
    {
        return new MultiModalEmotionCollection(MultiModalEmotion::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CreateMultiModalEmotionRequest $multiModalEmotionRequest
     * @return MultiModalEmotionResource
     */
    public function store(CreateMultiModalEmotionRequest $multiModalEmotionRequest)
    {
        $multiModalEmotion = new MultiModalEmotion();
        $validated_data = $multiModalEmotionRequest->validated();
        try{
            DB::beginTransaction();
            $multiModalEmotion->emotion()->associate(Emotion::find($validated_data["emotion_name"]));
            $multiModalEmotion->client()->associate(Auth::user()->userable);
            $multiModalEmotion->save();

            DB::commit();
            return new MultiModalEmotionResource($multiModalEmotion);
        }catch(\Throwable $th){
            DB::rollback();
            return response()->json(array(
                'code'      =>  400,
                'message'   =>  $th->getMessage()
            ), 400);
        }
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
     * Display the specified resource.
     *
     * @param   $multiModalEmotion
     * @return MultiModalEmotionResource
     */
    public function show($multiModalEmotion)
    {
        $multiModalEmotion = MultiModalEmotion::find($multiModalEmotion);
        return new MultiModalEmotionResource($multiModalEmotion);
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
     * @param  MultiModalEmotion $multiModalEmotion
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($multiModalEmotion)
    {
        $multiModalEmotion = MultiModalEmotion::findOrFail($multiModalEmotion);
        $multiModalEmotion->delete();

        return response()->json(array(
            'code'      =>  200,
            'message'   =>  "Multi modal emotion was removed"
        ), 200);
    }
}
