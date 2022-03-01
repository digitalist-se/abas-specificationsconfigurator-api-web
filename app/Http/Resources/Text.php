<?php

namespace App\Http\Resources;

use App\Models\Role;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 *  @mixin \App\Models\Text
 */
class Text extends JsonResource
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
            'key'    => $this->key,
            'value'  => $this->value,
            $this->mergeWhen($request->user()->role->is(Role::ADMIN), function () {
                return [
                    'id' => $this->id,
                    'locale' => $this->locale,
                    'description' => $this->description,
                ];
            }),
        ];
    }
}
