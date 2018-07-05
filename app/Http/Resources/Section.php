<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Element as ElementResource;

class Section extends JsonResource
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
        $data = [
            'id'                  => $this->id,
            'slug_name'           => $this->slug_name,
            'headline'            => $this->headline,
            'has_headline'        => $this->has_headline ?? false,
            'elements'            => ElementResource::collection($this->whenLoaded('appElements')),
            'illustration_states' => $this->illustration_states,
        ];
        if ($this->description) {
            $data['description'] = $this->description;
        }

        return $data;
    }
}
