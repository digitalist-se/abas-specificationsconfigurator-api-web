<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChoiceType as ChoiceTypeResource;
use App\Models\ChoiceType;

class ChoiceTypeController extends Controller
{
    public function list()
    {
        return ChoiceTypeResource::collection(ChoiceType::all());
    }
}
