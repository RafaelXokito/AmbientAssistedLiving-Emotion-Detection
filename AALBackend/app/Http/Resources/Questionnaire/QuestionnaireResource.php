<?php

namespace App\Http\Resources\Questionnaire;

use App\Http\Resources\ResponseQuestionnaire\ResponseQuestionnaireCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionnaireResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $questionnaire = $this->questionnaire;
        $parts = explode("\\", $questionnaire->questionnairable_type);
        $questionnaireType = end($parts);
        if($questionnaireType == "GeriatricQuestionnaire"){
            $questionnaireType = "Questionário de depressão geriátrica";
            if($questionnaire->points >= 0 && $questionnaire->points<=5){
                $emotionLevel = "Sem depressão";
            }
            else if($questionnaire->points >= 6 && $questionnaire->points<=10){
                $emotionLevel = "Depressão leve";
            }
            else{
                $emotionLevel = "Depressão severa";
            }
            $limit = "15";
        }else{
            // http://www.blake-group.com/sites/default/files/assessments/Oxford_Happiness_Questionnaire.pdf
            $questionnaireType = "Questionário de Felicidade de Oxford";
            if($questionnaire->points >= 0 && $questionnaire->points < 2){
                $emotionLevel = "Muito Infeliz";
            }
            else if($questionnaire->points >= 2 && $questionnaire->points < 3){
                $emotionLevel = "Um pouco Infeliz";
            }
            else if($questionnaire->points >= 3 && $questionnaire->points < 4){
                $emotionLevel = "Pouco Infeliz";
            }
            else if($questionnaire->points = 4){
                $emotionLevel = "Pouco feliz";
            }
            else if($questionnaire->points > 4 && $questionnaire->points < 5){
                $emotionLevel = "Bastante Feliz";
            }
            else if($questionnaire->points >= 5 && $questionnaire->points < 6){
                $emotionLevel = "Muito Feliz";
            }
            else if($questionnaire->points == 6){
                $emotionLevel = "Demasiado Feliz";
            }
            $limit = "6";
        }
        return [
            'id' => $this->id,
            'points' => "{$questionnaire->points}/{$limit}",
            'emotionLevel' => $emotionLevel,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'client_id' => $questionnaire->client_id,
            "questionnaire_type" => $questionnaireType,
            'responses' => new ResponseQuestionnaireCollection($questionnaire->responses)
        ];
    }
}
