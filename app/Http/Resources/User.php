<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
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
            'first_name'             => $this->first_name,
            'last_name'              => $this->last_name,
            'email'                  => $this->email,
            'email_verified'         => $this->hasVerifiedEmail(),
            'role'                   => $this->role->getValue(),
            'sex'                    => $this->sex,
            'company_name'           => $this->company_name,
            'phone'                  => $this->phone,
            'website'                => $this->website,
            'street'                 => $this->street,
            'additional_street_info' => $this->additional_street_info,
            'zipcode'                => $this->zipcode,
            'city'                   => $this->city,
            'contact_first_name'     => $this->contact_first_name,
            'contact_last_name'      => $this->contact_last_name,
            'contact_function'       => $this->contact_function,
            'country'                => $this->country,
        ];
    }
}
