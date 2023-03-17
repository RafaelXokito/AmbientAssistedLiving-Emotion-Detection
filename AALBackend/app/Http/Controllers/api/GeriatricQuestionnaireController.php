<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\GeriatricQuestionnaire;
use App\Http\Resources\GeriatricQuestionnaire\GeriatricQuestionnaireResource;
use App\Http\Resources\GeriatricQuestionnaire\GeriatricQuestionnaireCollection;
use App\Http\Requests\GeriatricQuestionnaire\CreateGeriatricQuestionnaireRequest;
use App\Models\ResponseGeriatricQuestionnaire;
use App\Models\Speech;

class GeriatricQuestionnaireController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return GeriatricQuestionnaireCollection
     */
    public function index()
    {
        return new GeriatricQuestionnaireCollection(Auth::user()->userable->questionnaires()->orderBy('created_at', 'desc')->get());
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
     * @param  GeriatricQuestionnaire $geriatricQuestionnaire
     * @return GeriatricQuestionnaireResource
     */
    public function show($geriatricQuestionnaire)
    {
        $questionnaire = GeriatricQuestionnaire::find($geriatricQuestionnaire);
        return new GeriatricQuestionnaireResource($questionnaire);
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

    public function store(CreateGeriatricQuestionnaireRequest $request)
    {
        $questionnaire = new GeriatricQuestionnaire();
        $validated_data = $request->validated();

        try {
            DB::beginTransaction();

            $questionnaire->points = $validated_data["points"];
            $questionnaire->client()->associate(Auth::user()->userable);
            $questionnaire->save();
            $responses = $validated_data["responses"];
            for ($i = 0; $i < count($responses); $i++) {
                $response = new ResponseGeriatricQuestionnaire();
                $jsonResponse = json_decode($responses[$i]);
                $response->is_why = $jsonResponse->is_why;
                $response->response = $jsonResponse->response;
                $response->question = $jsonResponse->question;
                $response->geriatric_questionnaire()->associate($questionnaire);
                $speech = Speech::findOrFail($jsonResponse->speech_id);
                if(!($speech->text === $response->response)){
                    return response()->json(array(
                        'code'      =>  422,
                        'message'   =>  "Speech Text is diferent than questionnaire response."
                    ), 422);
                }
                $response->speech()->associate($speech);
                $response->save();

            }
            DB::commit();

            return new GeriatricQuestionnaireResource($questionnaire);
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
     * @param  EmotionsNotification $emotionNotification
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($geriatricQuestionnaire)
    {
        $questionnaire = GeriatricQuestionnaire::findOrFail($geriatricQuestionnaire);
        $questionnaire->delete();

        return response()->json(array(
            'code'      =>  200,
            'message'   =>  "Geriatric Questionnaire was removed"
        ), 200);
    }
}
