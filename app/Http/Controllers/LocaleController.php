<?php

namespace App\Http\Controllers;

use App\Models\Locale;

class LocaleController extends Controller
{
    public function supported()
    {
        return response()->json(Locale::supportedSet()->getValues());
    }

    public function activated()
    {
        return response()->json(Locale::activatedSet()->getValues());
    }
}
