<?php

namespace App\Http\Controllers;

use App\Models\Locale;
use App\Models\Role;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function supported()
    {
        return response()->json(Locale::supportedSet()->getValues());
    }

    public function activated(Request $request)
    {
        $optionalUser = optional($request->user());
        $isAdmin = $optionalUser->role?->is(Role::ADMIN) === true;
        $localeSet = $isAdmin ? Locale::supportedSet() : Locale::activatedSet();

        return response()->json($localeSet->getValues());
    }
}
