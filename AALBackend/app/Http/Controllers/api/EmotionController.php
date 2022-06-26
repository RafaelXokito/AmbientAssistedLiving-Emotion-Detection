<?php

namespace App\Http\Controllers\api;

use App\Http\Requests\Emotion\CreateEmotionRequest;
use App\Http\Resources\Emotion\EmotionCollection;
use App\Http\Resources\Emotion\EmotionResource;
use App\Models\Emotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmotionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return EmotionCollection
     */
    public function index()
    {
        return new EmotionCollection(Emotion::all());
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
     * @return EmotionResource|\Illuminate\Http\JsonResponse
     */
    public function store(CreateEmotionRequest $request)
    {
        $emotion = new Emotion();
        $validated_data = $request->validated();

        try {
            DB::beginTransaction();

            $emotion->name = strtolower($validated_data["name"]);
            $emotion->category = strtolower($validated_data["group"]);

            $emotion->save();
            DB::commit();

            return new EmotionResource($emotion);
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
     * @param  Emotion  $emotion
     * @return EmotionResource
     */
    public function show(Emotion $emotion)
    {
        return new EmotionResource($emotion);
    }

    /**
     * Display the specified resource.
     *
     * @param string $group
     * @return EmotionCollection
     */
    public function showEmotionsByGroup(string $group)
    {
        return new EmotionCollection(Emotion::where("category", $group)->get());
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
    public function destroy(Emotion $emotion)
    {
        $emotion->destroy();
    }
}
