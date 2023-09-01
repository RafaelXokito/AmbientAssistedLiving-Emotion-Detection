<?php

namespace App\Http\Resources\Message;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
   /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $client = $this->client->user->email;
        return [
            "id" => $this->id,
            "isChatbot" => $this->isChatbot,
            "client" => $client,
            "body" => $this->body,
            "body" => $this->body,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at
        ];
    }
}
