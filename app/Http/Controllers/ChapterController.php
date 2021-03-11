<?php

namespace App\Http\Controllers;

use App\Http\Resources\Chapter as ChapterResource;
use App\Models\Chapter;

class ChapterController extends Controller
{
    public function list()
    {
        $chapters = Chapter::where('visible', '=', true)->orderBy('sort')->get();

        return ChapterResource::collection($chapters);
    }
}
