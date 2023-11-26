<?php

namespace App\Http\Resources\Iteration;

use App\Http\Resources\Frame\FrameResource;
use App\Http\Resources\Client\ClientResource;
use App\Http\Resources\Frame\FrameCollection;
use App\Http\Resources\Emotion\EmotionResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Content\ContentCollection;

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
        $details = $request->input('details') != null;
        FrameResource::$format = IterationResource::$format;
        $iteration = [
            'id' => $this->id,
            'macaddress' => $this->macaddress,
            'type' => $this->type,
            'emotion' => new EmotionResource($this->emotion),
            'created_at' => $this->created_at
        ];            

        switch (FrameResource::$format) {
            case "show_usage_id":
                $iteration['usage_id'] = $this->usage_id;
                break;
            default:
                if($details){
                    $iteration['contents'] = new ContentCollection($this->contents);
                }
        }
        return $iteration;
    }
}
