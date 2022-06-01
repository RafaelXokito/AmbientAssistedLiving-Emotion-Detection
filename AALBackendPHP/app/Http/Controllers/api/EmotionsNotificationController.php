<?php

namespace App\Http\Controllers\api;

use App\Models\Client;
use App\Models\Emotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\EmotionsNotification;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\EmotionNotification\EmotionNotificationResource;
use App\Http\Resources\EmotionNotification\EmotionNotificationCollection;
use App\Http\Requests\EmotionNotification\CreateEmotionNotificationRequest;

class EmotionsNotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return EmotionNotificationCollection
     */
    public function index()
    {
        return new EmotionNotificationCollection(EmotionsNotification::all());
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
     * @param  CreateEmotionNotificationRequest $emotionNotificationRequest
     * @return EmotionNotificationResource
     */
    public function store(CreateEmotionNotificationRequest $emotionNotificationRequest)
    {
        $emotionNotification = new EmotionsNotification();
        $validated_data = $emotionNotificationRequest->validated();

        try{
            DB::beginTransaction();
            $emotionNotification->emotion()->associate(Emotion::find($validated_data["emotion_name"]));
            $emotionNotification->client()->associate(Auth::user()->userable);

            $emotionNotification->accuracylimit = $validated_data["accuracyLimit"];
            $emotionNotification->duration = $validated_data["durationSeconds"];
            $emotionNotification->save();

            DB::commit();
            return new EmotionNotificationResource($emotionNotification);
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
     * @param  EmotionsNotification $emotionNotification
     * @return EmotionNotificationResource
     */
    public function show($emotionNotification)
    {
        $emotionNotification = EmotionsNotification::find($emotionNotification);
        return new EmotionNotificationResource($emotionNotification);
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
     * @param  EmotionsNotification $emotionNotification
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($emotionNotification)
    {
        $emotionNotification = EmotionsNotification::findOrFail($emotionNotification);
        $emotionNotification->delete();

        return response()->json(array(
            'code'      =>  200,
            'message'   =>  "Emotion notification was removed"
        ), 200);
    }
}
