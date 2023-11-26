<?php

namespace App\Http\Controllers\api;

use App\Models\Speech;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\OxfordHappinessQuestionnaire;
use App\Models\ResponseQuestionnaire;
use App\Models\QuestionnaireType;
use App\Http\Resources\Questionnaire\QuestionnaireResource;
use App\Http\Resources\Questionnaire\QuestionnaireCollection;
use App\Http\Resources\Question\QuestionCollection;
use App\Http\Resources\ResultMapping\ResultMappingCollection;
use App\Http\Requests\OxfordHappinessQuestionnaire\UpdatePointsOxfordHappinessQuestionnaireRequest;
use App\Http\Requests\ResponseQuestionnaire\CreateResponseOxfordHappinessQuestionnaire;

class OxfordHappinessQuestionnaireController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return QuestionnaireCollection
     */
    public function index()
    {
        $oh_questionnaires = OxfordHappinessQuestionnaire::join('questionnaires', 'questionnaires.questionnairable_id', '=', 'oh_questionnaires.id')
        ->where("questionnaires.questionnairable_type", "App\\Models\\OxfordHappinessQuestionnaire")
        ->where("questionnaires.client_id", Auth::user()->userable->id)
        ->select('oh_questionnaires.*')
        ->orderBy('questionnaires.created_at', 'desc')
        ->get();

        return new QuestionnaireCollection($oh_questionnaires);
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
        $oh_questionnaire = OxfordHappinessQuestionnaire::findorFail($Questionnaire);
        return new QuestionnaireResource($oh_questionnaire);
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
        $oh_questionnaire = new OxfordHappinessQuestionnaire();

        try {
            DB::beginTransaction();
            $oh_questionnaire->save();
            $oh_questionnaire->questionnaire()->create([
                'client_id' => Auth::user()->userable->id
            ]);
            $oh_questionnaire->save();
            DB::commit();

            return new QuestionnaireResource($oh_questionnaire);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(array(
                'code'      =>  400,
                'message'   =>  $th->getMessage()
            ), 400);
        }
    }

    public function updatePoints(UpdatePointsOxfordHappinessQuestionnaireRequest $request, $Questionnaire){
        $oh_questionnaire = OxfordHappinessQuestionnaire::findorFail($Questionnaire);
        $validated_data = $request->validated();

        try {
            DB::beginTransaction();
            $oh_questionnaire->save();
            $oh_questionnaire->questionnaire()->update([
                'points' => $validated_data["points"]
            ]);
            $oh_questionnaire->save();
            DB::commit();

            return new QuestionnaireResource($oh_questionnaire);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(array(
                'code'      =>  400,
                'message'   =>  $th->getMessage()
            ), 400);
        }
    }


    public function updateResponses(CreateResponseOxfordHappinessQuestionnaire $request, $Questionnaire){
        $oh_questionnaire = OxfordHappinessQuestionnaire::findorFail($Questionnaire);
        $validated_data = $request->validated();

        $responseQuestionExists = ResponseQuestionnaire::where('questionnaire_id', '=', $oh_questionnaire->questionnaire->id)
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
            $response->questionnaire()->associate($oh_questionnaire->questionnaire->id);
            $speech = Speech::findOrFail($validated_data["speech_id"]);
            if(!($speech->text === $response->response)){
                return response()->json(array(
                    'code'      =>  422,
                    'message'   =>  "Speech Text is diferent than questionnaire response."
                ), 422);
            }
            $response->speech()->associate($speech);
            $response->save();

            $oh_questionnaire->save();
            DB::commit();

            return new QuestionnaireResource($oh_questionnaire);
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
        $questionnaire = OxfordHappinessQuestionnaire::findOrFail($Questionnaire);
        $questionnaire->delete();

        return response()->json(array(
            'code'      =>  200,
            'message'   =>  "Oxford Happiness Questionnaire was removed"
        ), 200);
    }
}