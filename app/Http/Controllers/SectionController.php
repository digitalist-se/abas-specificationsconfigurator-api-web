<?php

namespace App\Http\Controllers;

use App\Http\Resources\Section as SectionResource;
use App\Models\Chapter;

class SectionController extends Controller
{
    public function list(Chapter $chapter)
    {
        $sections = $chapter->sections()->orderBy('sort')->get();

        return SectionResource::collection($sections);
    }
}
