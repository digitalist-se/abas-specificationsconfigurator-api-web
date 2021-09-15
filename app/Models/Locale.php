<?php

namespace App\Models;

use Illuminate\Support\Facades\App;
use MabeEnum\Enum;
use MabeEnum\EnumSet;

/**
 * \App\Models\Locale.
 *
 * @method static \App\Models\Locale DE()
 * @method static \App\Models\Locale EN()
 */
class Locale extends Enum
{
    const DE = 'de';
    const EN  ='en';

    /**
     * @return \MabeEnum\EnumSet
     */
    public static function supportedSet(): EnumSet {
        $supportedLocales = new EnumSet(static::class);
        $supportedLocales->attach(static::DE());
        $supportedLocales->attach(static::EN());
        return $supportedLocales;
    }

    public static function current() {
        $locale = config('app.fallback_locale');
        $currentLocale = App::currentLocale();
        if (Locale::supportedSet()->contains($currentLocale)) {
            $locale = $currentLocale;
        }
        return static::byValue($locale);
    }
}
