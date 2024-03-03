<?php

namespace App\Http\Resources\Content;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Message\MessageResource;
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
            case "Message":
                return [
                    "emotion" => new EmotionResource($this->emotion),
                    "accuracy" => $this->accuracy,
                    "createdate" => $this->createdate,
                    "type" => "Message",
                    "content" => new MessageResource($child)
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
