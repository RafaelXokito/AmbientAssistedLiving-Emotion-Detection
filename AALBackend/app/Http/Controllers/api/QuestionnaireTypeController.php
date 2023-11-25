<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\QuestionnaireType;
use App\Http\Resources\QuestionnaireType\QuestionnaireTypeCollection;
use App\Http\Resources\QuestionnaireType\QuestionnaireTypeResource;

class QuestionnaireTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return QuestionnaireTypeCollection
     */
    public function index()
    {
        $questionnaires_types = QuestionnaireType::all();
        return new QuestionnaireTypeCollection($questionnaires_types);
    }

    /**
     * Display a listing of the resource.
     *
     * @return QuestionnaireTypeResource
     */
    public function show(QuestionnaireType $questionnaire)
    {
        return new QuestionnaireTypeResource($questionnaire);
    }
}