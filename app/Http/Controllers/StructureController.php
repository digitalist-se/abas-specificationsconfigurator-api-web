<?php

namespace App\Http\Controllers;

use App\Http\Resources\Chapter as ChapterResource;
use App\Models\Chapter;

class StructureController extends Controller
{
    public function get()
    {
        $chapters = Chapter::where('visible', '=', true)->with([
            'sections',
            'sections.appElements',
        ])->orderBy('sort')->get();

        return ChapterResource::collection($chapters);
    }
}
