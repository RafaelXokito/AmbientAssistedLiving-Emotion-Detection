<?php

namespace App\Http\Resources\Emotion;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\EmotionNotification\EmotionNotificationCollection;

class EmotionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'category' => $this->category,
            'weight' => $this->weight,
            'display_name' => $this->display_name,
            'display_group' => $this->display_group,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'notificationsSettings' => new EmotionNotificationCollection($this->emotionNotifications),
        ];
    }
}

