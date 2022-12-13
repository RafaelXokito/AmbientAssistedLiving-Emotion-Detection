<?php

namespace App\Http\Resources\Frame;

use App\Models\Frame;
use App\Models\Emotion;
use App\Http\Resources\Client\ClientResource;
use App\Http\Resources\Emotion\EmotionResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Classification\ClassificationResource;
use App\Http\Resources\Classification\ClassificationCollection;

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
                        'emotion' => new EmotionResource($this->content->emotion ?? new Emotion()),
                        'createDate' => $this->content->createdate,
                        'emotionIteration' => new EmotionResource($this->content->iteration->emotion ?? new Emotion()),
                        'predictions' => new ClassificationCollection($this->content->classifications),
                    ];
            case 'graph':
                return [
                    'id' => $this->id,
                    'emotion_predicted' => $this->content->iteration->emotion->name,
                    'emotion_classified' => $this->content->emotion->name ?? "N/A",
                    'accuracy' => $this->content->accuracy,
                    'createDate' => $this->content->createdate,
                ];
            default:
                return [
                    'id' => $this->id,
                    'name' => $this->name,
                    'path' => $this->path,
                    'emotion' => $this->content->emotion->name ?? "",
                    'emotion_iteration' => $this->content->iteration->emotion->name ?? "",
                    'accuracy' => $this->content->classifications[0]->accuracy ?? "N\A",
                    'createDate' => $this->content->createdate,
                ];
        }
    }
}
