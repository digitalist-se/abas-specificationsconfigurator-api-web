<?php

namespace App\Http\Controllers;

use App\Http\Resources\Text as TextResource;
use App\Models\Role;
use App\Models\Text;
use Illuminate\Http\Request;

class TextController extends Controller
{
    public function list(Request $request)
    {
        if ($request->user()->role->is(Role::ADMIN)) {
            $texts = Text::all()->sortBy('key');
        } else {
            // only for app relevant texts
            $texts = Text::where('public', '=', true)->orderBy('key')->get();
        }
        $data  = [];
        foreach ($texts as $text) {
            $data[$text->key] = TextResource::make($text);
        }

        return response()->json((object) $data);
    }

    public function update(Request $request, Text $text)
    {
        $this->validate($request, [
            'value' => 'required',
        ]);
        $text->value = $request->input('value');
        $text->saveOrFail();

        return response('', 204);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'key'   => 'required',
            'value' => 'required',
        ]);
        $public = $request->input('public') ?? true;
        $data   = [
            'key'    => $request->input('key'),
            'value'  => $request->input('value'),
            'public' => $public,
        ];
        Text::create($data);

        return response('', 204);
    }
}
