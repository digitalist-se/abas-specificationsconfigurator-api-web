<?php

namespace App\Models;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use MabeEnum\Enum;
use MabeEnum\EnumSet;
use Throwable;

/**
 * \App\Models\Locale.
 *
 * @method static Locale DE()
 * @method static Locale EN()
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
        $supportedLocales->attach(static::EN());
        $supportedLocales->attach(static::DE());
        return $supportedLocales;
    }

    /**
     * @return \MabeEnum\EnumSet
     */
    public static function activatedSet(): EnumSet {
        $activatedLocales = new EnumSet(static::class);
        foreach (config('app.activated_locales') as $configLocale) {
            try {
                $activatedLocales->attach(static::get($configLocale));
            } catch (Throwable $e) {
                Log::warning('Unrecognized locale ' . $configLocale);
            }
        }
        return $activatedLocales->intersect(static::supportedSet());
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
