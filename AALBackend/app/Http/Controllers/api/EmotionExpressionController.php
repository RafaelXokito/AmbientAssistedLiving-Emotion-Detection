<?php

namespace App\Http\Controllers\api;

use App\Http\Requests\EmotionExpression\CreateEmotionExpressionRequest;
use App\Http\Requests\EmotionExpression\UpdateEmotionExpressionRequest;
use App\Http\Resources\EmotionExpression\EmotionExpressionCollection;
use App\Http\Resources\EmotionExpression\EmotionExpressionResource;
use App\Models\Emotion;
use App\Models\EmotionExpression;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmotionExpressionController extends Controller
{
    //CreateEmotionExpressionRequest

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\EmotionExpressionCollection
     */
    public function index()
    {
        return new EmotionExpressionCollection(Auth::user()->userable->emotionExpressions()->orderBy('created_at', 'desc')->get());
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
     * @return \Illuminate\Http\Response
     */
    public function store(CreateEmotionExpressionRequest $request)
    {
        $validated_data = $request->validated();
        $emotion = Emotion::findOrFail($validated_data["emotion_name"]);

        try {
            DB::beginTransaction();

            $expression = new EmotionExpression();

            $expression->client()->associate(Auth::user()->userable);
            $expression->emotion()->associate($emotion);

            $expression->expression_name = $validated_data["expression_name"];

            $expression->save();

            DB::commit();
            return new EmotionExpressionResource($expression);

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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return new EmotionExpressionResource(EmotionExpression::findOrFail($id));
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
    public function update(UpdateEmotionExpressionRequest $request, $id)
    {
        $validated_data = $request->validated();
        $expression = EmotionExpression::findOrFail($id);

        try {
            DB::beginTransaction();

            $expression->expression_name = $validated_data["expression_name"];

            $expression->save();

            DB::commit();
            return new EmotionExpressionResource($expression);

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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


}
