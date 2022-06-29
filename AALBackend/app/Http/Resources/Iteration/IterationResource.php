<?php

namespace App\Http\Resources\Iteration;

use App\Http\Resources\Client\ClientResource;
use App\Http\Resources\Emotion\EmotionResource;
use App\Http\Resources\Frame\FrameCollection;
use App\Http\Resources\Frame\FrameResource;
use Illuminate\Http\Resources\Json\JsonResource;

class IterationResource extends JsonResource
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
        FrameResource::$format = IterationResource::$format;
        switch (FrameResource::$format) {
            case "show_usage_id":
                return [
                    'id' => $this->id,
                    'macaddress' => $this->macaddress,
                    'emotion' => new EmotionResource($this->emotion),
                    'created_at' => $this->created_at,
                    'client' => new ClientResource($this->client),
                    'frames' => new FrameCollection($this->frames),
                    'usage_id' => $this->usage_id
                ];
                break;
            default:
                return [
                    'id' => $this->id,
                    'macaddress' => $this->macaddress,
                    'emotion' => new EmotionResource($this->emotion),
                    'created_at' => $this->created_at,
                    'client' => new ClientResource($this->client),
                    'frames' => new FrameCollection($this->frames),
                ];
        }
    }
}
