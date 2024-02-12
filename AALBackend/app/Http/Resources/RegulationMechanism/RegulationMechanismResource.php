<?php

namespace App\Http\Resources\RegulationMechanism;

use Illuminate\Http\Resources\Json\JsonResource;

class RegulationMechanismResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
