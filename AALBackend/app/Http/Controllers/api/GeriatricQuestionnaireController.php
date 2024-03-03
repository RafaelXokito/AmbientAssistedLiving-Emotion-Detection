<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\GeriatricQuestionnaire;
use App\Http\Resources\Questionnaire\QuestionnaireResource;
use App\Http\Resources\Questionnaire\QuestionnaireCollection;

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
        abort(404);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  Questionnaire $Questionnaire
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
