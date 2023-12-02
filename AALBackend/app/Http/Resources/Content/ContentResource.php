<?php

namespace App\Http\Resources\Content;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ResponseQuestionnaire\ResponseQuestionnaireResource;
use App\Http\Resources\Frame\FrameResource;
use App\Http\Resources\Speech\SpeechResource;
use App\Http\Resources\Emotion\EmotionResource;

class ContentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $model = explode('\\',$this->childable_type);
        $child = $this->childable;
        // Content
        switch(end($model)){
            case "ResponseQuestionnaire":
                return [
                    "emotion" => new EmotionResource($this->emotion),
                    "accuracy" => $this->accuracy,
                    "createdate" => $this->createdate,
                    "type" => "ResponseQuestionnaire",
                    "content" => new ResponseQuestionnaireResource($child)
                ];
            case "Frame":
                return [
                    "emotion" => new EmotionResource($this->emotion),
                    "accuracy" => $this->accuracy,
                    "createdate" => $this->createdate,
                    "type" => "Frame",
                    "content" => new FrameResource($child)
                ];
            case "Speech":
                return [
                    "emotion" => new EmotionResource($this->emotion),
                    "accuracy" => $this->accuracy,
                    "createdate" => $this->createdate,
                    "type" => "Speech",
                    "content" => new SpeechResource($child)
                ];
        }
        return parent::toArray($request);
    }
}
