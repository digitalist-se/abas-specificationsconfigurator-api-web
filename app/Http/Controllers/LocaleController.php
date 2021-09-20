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
        $isAdmin = $request->user()->role->is(Role::ADMIN);
        $localeSet = $isAdmin ? Locale::supportedSet() : Locale::activatedSet();

        return response()->json($localeSet->getValues());
    }
}
