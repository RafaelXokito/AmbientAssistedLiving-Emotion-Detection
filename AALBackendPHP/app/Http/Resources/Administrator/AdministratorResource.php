<?php

namespace App\Http\Resources\Administrator;

use Illuminate\Http\Resources\Json\JsonResource;

class AdministratorResource extends JsonResource
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
            'id' => $this->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'created_at' => $this->user->created_at,
            'updated_at' => $this->user->updated_at,
            'deleted_at' => $this->user->deleted_at,
        ];
    }
}
