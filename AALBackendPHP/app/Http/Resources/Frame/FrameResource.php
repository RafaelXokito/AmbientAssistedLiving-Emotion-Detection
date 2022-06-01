<?php

namespace App\Http\Resources\Frame;

use App\Http\Resources\Classification\ClassificationCollection;
use App\Http\Resources\Classification\ClassificationResource;
use App\Http\Resources\Client\ClientResource;
use App\Http\Resources\Emotion\EmotionResource;
use App\Models\Emotion;
use Illuminate\Http\Resources\Json\JsonResource;

class FrameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public static $format = "default";
    public function toArray($request)
    {
        switch (FrameResource::$format) {
            case 'extended':
                return [
                    'id' => $this->id,
                    'filename' => $this->name,
                    'filepath' => $this->path,
                    'emotion' => new EmotionResource($this->emotion ?? new Emotion()),
                    'createDate' => $this->createdate,
                    'emotionIteration' => new EmotionResource($this->iteration->emotion ?? new Emotion()),
                    'predictions' => new ClassificationCollection($this->classifications),
                ];
            case 'graph':
                return [
                    'id' => $this->id,
                    'emotion_predicted' => $this->iteration->emotion->name,
                    'emotion_classified' => $this->emotion->name ?? "N/A",
                    'accuracy' => $this->accuracy,
                    'createDate' => $this->createdate,
                ];
            default:
                return [
                    'id' => $this->id,
                    'name' => $this->name,
                    'path' => $this->path,
                    'createdate' => $this->createdate,
                ];
        }
    }
}
