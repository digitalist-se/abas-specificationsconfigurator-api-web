<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Http\Resources\Section as SectionResource;

class SectionController extends Controller
{
    public function list(Chapter $chapter)
    {
        $sections = $chapter->sections()->orderBy('sort')->get();

        return SectionResource::collection($sections);
    }
}
