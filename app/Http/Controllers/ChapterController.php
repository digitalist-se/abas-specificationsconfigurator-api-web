<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Http\Resources\Chapter as ChapterResource;

class ChapterController extends Controller
{
    public function list()
    {
        $chapters = Chapter::where('visible', '=', true)->orderBy('sort')->get();

        return ChapterResource::collection($chapters);
    }
}
