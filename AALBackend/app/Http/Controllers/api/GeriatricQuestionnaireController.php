<?php

namespace App\Http\Controllers\api;

use App\Models\Speech;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\GeriatricQuestionnaire;
use App\Models\ResponseQuestionnaire;
use App\Http\Resources\Questionnaire\QuestionnaireResource;
use App\Http\Resources\Questionnaire\QuestionnaireCollection;
use App\Http\Requests\GeriatricQuestionnaire\UpdatePointsGeriatricQuestionnaireRequest;
use App\Http\Requests\ResponseQuestionnaire\CreateResponseGeriatricQuestionnaire;

class GeriatricQuestionnaireController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return QuestionnaireCollection
     */
    public function index()
    {
        $geriatric_questionnaires = GeriatricQuestionnaire::join('questionnaires', 'questionnaires.questionnairable_id', '=', 'geriatric_questionnaires.id')
        ->where("questionnaires.questionnairable_type", "App\\Models\\GeriatricQuestionnaire")
        ->where("questionnaires.client_id", Auth::user()->userable->id)
        ->select('geriatric_questionnaires.*')
        ->orderBy('questionnaires.created_at', 'desc')
        ->simplePaginate(30);

        return new QuestionnaireCollection($geriatric_questionnaires);
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
     * @param  Questionnaire $Questionnaire
     * @return QuestionnaireResource
     */
    public function show($Questionnaire)
    {   
        $geriatric_questionnaire = GeriatricQuestionnaire::findorFail($Questionnaire);
        return new QuestionnaireResource($geriatric_questionnaire);
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

    
    public function store(Request $request)
    {
        $geriatric_questionnaire = new GeriatricQuestionnaire();

        try {
            DB::beginTransaction();
            $geriatric_questionnaire->save();
            $geriatric_questionnaire->questionnaire()->create([
                'client_id' => Auth::user()->userable->id
            ]);
            $geriatric_questionnaire->save();
            DB::commit();

            return new QuestionnaireResource($geriatric_questionnaire);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(array(
                'code'      =>  400,
                'message'   =>  $th->getMessage()
            ), 400);
        }
    }

    public function updatePoints(UpdatePointsGeriatricQuestionnaireRequest $request, $Questionnaire){
        $geriatric_questionnaire = GeriatricQuestionnaire::findorFail($Questionnaire);
        $validated_data = $request->validated();

        try {
            DB::beginTransaction();
            $geriatric_questionnaire->save();
            $geriatric_questionnaire->questionnaire()->update([
                'points' => $validated_data["points"]
            ]);
            $geriatric_questionnaire->save();
            DB::commit();

            return new QuestionnaireResource($geriatric_questionnaire);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(array(
                'code'      =>  400,
                'message'   =>  $th->getMessage()
            ), 400);
        }
    }

    public function updateResponses(CreateResponseGeriatricQuestionnaire $request, $Questionnaire){
        $geriatric_questionnaire = GeriatricQuestionnaire::findorFail($Questionnaire);
        $validated_data = $request->validated();

        $responseQuestionExists = ResponseQuestionnaire::where('questionnaire_id', '=', $geriatric_questionnaire->questionnaire->id)
        ->where('question', '=', $validated_data["question"])
        ->exists();

        if($responseQuestionExists){
            return response()->json(array(
                'code'      =>  422,
                'message'   =>  "This question aready has a saved response"
            ), 422);
        }

        try {
            DB::beginTransaction();
            
            $response = new ResponseQuestionnaire();
            $response->is_why = $validated_data["is_why"];
            $response->response = $validated_data["response"];
            $response->question = $validated_data["question"];
            $response->questionnaire()->associate($geriatric_questionnaire->questionnaire->id);
            $speech = Speech::findOrFail($validated_data["speech_id"]);
            if(!($speech->text === $response->response)){
                return response()->json(array(
                    'code'      =>  422,
                    'message'   =>  "Speech Text is diferent than questionnaire response."
                ), 422);
            }
            $response->speech()->associate($speech);
            $response->save();

            $geriatric_questionnaire->save();
            DB::commit();

            return new QuestionnaireResource($geriatric_questionnaire);
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
    public function destroy($Questionnaire)
    {
        $questionnaire = GeriatricQuestionnaire::findOrFail($Questionnaire);
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

     public function evaluateSAModel($Questionnaire){
        $questionnaire = GeriatricQuestionnaire::find($Questionnaire)->questionnaire;
        $responses = DB::table('responses_questionnaire')
            ->join('speeches', 'responses_questionnaire.speech_id', '=', 'speeches.id')
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
            ->where('responses_questionnaire.questionnaire_id','=',$questionnaire->id)
            ->get();
        $occurrences = [];
        foreach ($responses as $response) {
            if (!isset($occurrences[$response->emotion_name])) {
                $occurrences[$response->emotion_name] = 0;
            }else{
                $occurrences[$response->emotion_name] += 1;
            }
        }
        //inclusive
        $emotionPointsMapping = [
            //0-5
            'happy' => 5,
            
            // 6-10
            'disgust' => 10,
            'shame' => 10,
            'angry' => 10,
            'guilt' => 10,
            
            // 11-15
            'fear' => 15,
            'sad' => 15,
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
