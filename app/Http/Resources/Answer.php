<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Answer extends JsonResource
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
            'elementId' => $this->element_id,
            'value'     => $this->value,
            'updatedAt' => $this->updated_at->getTimestamp() * 1000,
        ];
    }
}
