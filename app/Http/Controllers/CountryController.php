<?php

namespace App\Http\Controllers;

use App\Http\Resources\Country as CountryResource;
use App\Models\Country;

class CountryController
{
    public function index()
    {
        return CountryResource::collection(Country::cases());
    }
}
