<?php

namespace App\Http\Controllers\api;

use App\Models\Speech;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\GeriatricQuestionnaire;
use App\Models\ResponseGeriatricQuestionnaire;
use App\Http\Resources\GeriatricQuestionnaire\GeriatricQuestionnaireResource;
use App\Http\Resources\GeriatricQuestionnaire\GeriatricQuestionnaireCollection;
use App\Http\Requests\GeriatricQuestionnaire\CreateGeriatricQuestionnaireRequest;

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


    /**
     * Compare the questionnaire points with the responses detected emotions wiht SA
     *
     * @return array
     */

     public function evaluateSAModel($geriatricQuestionnaire){
        $questionnaire = GeriatricQuestionnaire::find($geriatricQuestionnaire);
        $responses = DB::table('responses_geriatric_questionnaire')
            ->join('speeches', 'responses_geriatric_questionnaire.speech_id', '=', 'speeches.id')
            ->join('contents', function ($join) {
                $join->on('contents.childable_id', '=', 'speeches.id')
                     ->where('contents.childable_type', '=', 'App\\Models\\Speech');
            })
            ->leftJoin('classifications', function ($join) {
                $join->on('classifications.content_id', '=', 'contents.id')
                     ->whereRaw('classifications.accuracy = (SELECT MAX(accuracy) FROM classifications WHERE content_id = contents.id)');
            })
            ->select(
            'classifications.accuracy',
            'classifications.emotion_name')
            ->where('responses_geriatric_questionnaire.questionnaire_id','=',$questionnaire->id)
            ->get();
        $occurrences = [];
        foreach ($responses as $response) {
            if (!isset($occurrences[$response->emotion_name])) {
                $occurrences[$response->emotion_name] = 0;
            }else{
                $occurrences[$response->emotion_name] += 1;
            }
        }
        $emotionPointsMapping = [
            'angry' => 15,
            'disgust' => 15,
            'fear' => 15,
            'guilt' => 10,
            'happy' => 5,
            'sad' => 10,
            'shame' => 10,
        ];

        $prevalentEmotionsVal = max($occurrences);
        $prevalentEmotions = array_keys($occurrences, $prevalentEmotionsVal);

        //$occurrences = json_encode($occurrences);
        $emotionPointsMapping = $emotionPointsMapping[$prevalentEmotions[0]];

        $value = [];
        if($questionnaire->points >= 0 && $questionnaire->points <= 5){
            $value = 5;
        }elseif($questionnaire->points >= 6 && $questionnaire->points <= 10){
            $value = 10;
        }else{
            $value = 15;
        }
        $graphData = (object)[
           "data" => (object)[
                "points" => intval($questionnaire->points),
                "points_range" => $value,
                "sa_highest_emo_range" => $emotionPointsMapping
           ]
        ];

        return $graphData;
     }
}
