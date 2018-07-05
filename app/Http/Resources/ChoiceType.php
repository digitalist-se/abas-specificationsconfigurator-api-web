<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChoiceType extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'       => $this->id,
            'type'     => $this->type,
            'multiple' => $this->multiple,
            'tiles'    => $this->tiles,
            'options'  => $this->options,
        ];
    }
}
