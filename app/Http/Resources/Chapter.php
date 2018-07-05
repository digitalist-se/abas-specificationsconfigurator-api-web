<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Section as SectionResource;

class Chapter extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'slug_name'           => $this->slug_name,
            'sections'            => SectionResource::collection($this->whenLoaded('sections')),
            'description'         => $this->description,
            'illustration_states' => $this->illustration_states,
        ];
    }
}
