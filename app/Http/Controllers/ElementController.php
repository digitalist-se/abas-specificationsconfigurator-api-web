<?php

namespace App\Http\Controllers;

use App\Http\Resources\Element as ElementResource;
use App\Models\Section;

class ElementController extends Controller
{
    public function list(Section $section)
    {
        $elements = $section->elements()->orderBy('sort')->get();

        return ElementResource::collection($elements);
    }
}
