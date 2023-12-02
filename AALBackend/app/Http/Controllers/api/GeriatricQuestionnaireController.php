<?php

namespace App\Http\Controllers\api;

use App\Models\Speech;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\GeriatricQuestionnaire;
use App\Models\ResponseQuestionnaire;
use App\Models\QuestionnaireType;
use App\Models\Iteration;
use App\Http\Resources\Questionnaire\QuestionnaireResource;
use App\Http\Resources\Questionnaire\QuestionnaireCollection;
use App\Http\Resources\Question\QuestionCollection;
use App\Http\Resources\ResultMapping\ResultMappingCollection;
use App\Http\Requests\GeriatricQuestionnaire\UpdatePointsGeriatricQuestionnaireRequest;
use App\Http\Requests\ResponseQuestionnaire\CreateResponseQuestionnaire;
use Illuminate\Support\Facades\Validator;

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
        ->get();
        
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

    public function updateResponses(CreateResponseQuestionnaire $request, $Questionnaire){
        $geriatric_questionnaire = GeriatricQuestionnaire::findorFail($Questionnaire);
        $validated_data = $request->validated();

        

        $responseQuestionExists = ResponseQuestionnaire::where('questionnaire_id', '=', $geriatric_questionnaire->questionnaire->id)
        ->where('question', '=', $validated_data["question"])
        ->where('is_why', '=', $validated_data["is_why"])
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
           
            $response->save();
            if($response->is_why == TRUE){

                $validator = Validator::make($request->all(), [
                    'accuracy' => ['required','numeric','between:0.00,100.00'],
                    'created_at' => ['required', 'date_format:Y-m-d H:i:s'],
                    'iteration_id' => ['required','integer','exists:iterations,id'],
                    'iteration_usage_id' => ['required','string','exists:iterations,usage_id'],
                ]);
                
                if ($validator->fails()) {
                    return response()->json($validator->errors(), 422);
                }
                
                $iteration = Iteration::findOrFail($validated_data["iteration_id"]);
                if ($iteration->usage_id != $validated_data["iteration_usage_id"] && !Carbon::createFromTimestamp($iteration->created_at)->addMinutes(30)->gt(Carbon::now()))
                    return response()->json(array(
                        'code'      =>  422,
                        'message'   =>  "Iteration Usage Id doesnt match or expired!"
                    ), 422);
                
                
                $response->content()->create([
                    'emotion_name' => $iteration->emotion_name,
                    'accuracy' => $validated_data["accuracy"],
                    'createdate' => $validated_data["created_at"],
                    'iteration_id' => $iteration->id
                ]);
            }    

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
        $questionnaire->questionnaire->responses()->delete();
        $questionnaire->questionnaire()->delete();
        $questionnaire->delete();

        return response()->json(array(
            'code'      =>  200,
            'message'   =>  "Geriatric Questionnaire was removed"
        ), 200);
    }
}
