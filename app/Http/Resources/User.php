<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
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
            'name'                   => $this->name,
            'email'                  => $this->email,
            'role'                   => $this->role->getValue(),
            'sex'                    => $this->sex,
            'company_name'           => $this->company_name,
            'phone'                  => $this->phone,
            'website'                => $this->website,
            'street'                 => $this->street,
            'additional_street_info' => $this->additional_street_info,
            'zipcode'                => $this->zipcode,
            'city'                   => $this->city,
            'contact'                => $this->contact,
            'contact_function'       => $this->contact_function,
        ];
    }
}
