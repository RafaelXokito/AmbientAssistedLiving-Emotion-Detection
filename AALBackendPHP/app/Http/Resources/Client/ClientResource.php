<?php

namespace App\Http\Resources\Client;

use App\Http\Resources\Administrator\AdministratorResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
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
            'birth_date' => $this->birthdate,
            'contact' => $this->contact,
            'administrator' => new AdministratorResource($this->administrator),
            'created_at' => $this->user->created_at,
            'updated_at' => $this->user->updated_at,
            'deleted_at' => $this->user->deleted_at,
        ];
    }
}
