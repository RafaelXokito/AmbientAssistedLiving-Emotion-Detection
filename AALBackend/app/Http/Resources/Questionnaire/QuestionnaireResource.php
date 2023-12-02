<?php

namespace App\Http\Resources\Questionnaire;

use App\Http\Resources\ResponseQuestionnaire\ResponseQuestionnaireCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\QuestionnaireType;
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
        $questionnaireType = QuestionnaireType::where('questionnairable_model_name',$this->questionnaire->questionnairable_type)->first();
            $finalMessage = "";
            $result_mappings = $questionnaireType->result_mappings;
            foreach ($result_mappings as $result_mapping) {
                if($this->questionnaire->points < $result_mapping->points_min) continue;
                if($result_mapping->points_max_inclusive ){
                    if($this->questionnaire->points <= $result_mapping->points_max){
                        $finalMessage = $result_mapping->short_message;
                    }
                }else{
                    if($this->questionnaire->points < $result_mapping->points_max){
                        $finalMessage = $result_mapping->short_message;
                    }
                }
            }

        if($request->input('details') == null){
            return [
                'id' => $this->id,
                'questionnairable_id' => $this->questionnaire->id,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'points' => $this->questionnaire->points,
                'short_message' => $finalMessage,
                'client_id' => $this->questionnaire->client_id
            ];
        }else{
            
            return [
                'id' => $this->id,
                'questionnairable_id' => $this->questionnaire->id,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'points' => $this->questionnaire->points,
                'short_message' => $finalMessage,
                'client_id' => $this->questionnaire->client_id,
                'responses' => new ResponseQuestionnaireCollection($this->questionnaire->responses)
            ];
        }
        
    }
}
