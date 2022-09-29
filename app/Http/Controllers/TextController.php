<?php

namespace App\Http\Controllers;

use App\Http\Resources\Text as TextResource;
use App\Models\Locale;
use App\Models\Role;
use App\Models\Text;
use App\Rules\IsSupportedLocale;
use Illuminate\Http\Request;

class TextController extends Controller
{
    public function list(Request $request)
    {
        $this->validate($request, [
            'locale' => ['sometimes', 'required', new IsSupportedLocale],
        ]);

        $locale = $request->input('locale', Locale::current()->getValue());

        if ($request->user()->role->is(Role::ADMIN)) {
            $texts = Text::whereLocale($locale)
                ->orderBy('key')
                ->get();
        } else {
            // only for app relevant texts
            $texts = Text::whereLocale($locale)
                ->wherePublic(true)
                ->orderBy('key')
                ->get();
        }

        $data = [];
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
            'key'    => 'required',
            'locale' => ['required', new IsSupportedLocale],
            'value'  => 'required',
        ]);
        $public = $request->input('public') ?? true;
        $data = [
            'key'    => $request->input('key'),
            'locale' => $request->input('locale'),
            'value'  => $request->input('value'),
            'public' => $public,
        ];
        Text::create($data);

        return response('', 204);
    }
}
