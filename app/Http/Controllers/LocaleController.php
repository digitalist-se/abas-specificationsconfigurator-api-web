<?php

namespace App\Http\Controllers;

use App\Models\Locale;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function list()
    {
        return response()->json(Locale::supportedSet()->getValues());
    }
}
