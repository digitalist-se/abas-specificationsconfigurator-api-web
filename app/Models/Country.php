<?php

namespace App\Models;

use Illuminate\Support\Str;

enum Country: string
{
    case Australia = 'au';
    case Austria = 'at';
    case Bulgaria = 'bg';
    case Canada = 'ca';
    case China = 'cn';
    case Czech_Republic = 'cz';
    case France = 'fr';
    case Germany = 'de';
    case Hungary = 'hu';
    case India = 'in';
    case Italy = 'it';
    case Malaysia = 'my';
    case Netherlands = 'nl';
    case Poland = 'pl';
    case Romania = 'ro';
    case Singapore = 'sg';
    case Slovakia = 'sk';
    case Spain = 'es';
    case Switzerland = 'ch';
    case Turkey = 'tr';
    case United_States = 'us';
    case Other = 'other';

    public function getName(): string
    {
        return str_replace('_', ' ', $this->name);
    }

    public function getDisplayName(?string $locale = null): string
    {
        if (! $locale) {
            $locale = app()->getLocale();
        }

        if ($this === self::Other) {
            return __('Other', locale: $locale);
        }

        $countryLocale = '-'.Str::upper($this->value);

        return \Locale::getDisplayRegion($countryLocale, $locale);
    }

    public static function findMatch($key): Country
    {
        $match = collect(self::cases())
            ->first(function (Country $case) use ($key) {
                return $key === $case->value
                    || $key === str_replace('_', ' ', $case->getName());
            });

        if ($match) {
            return $match;
        }
        $match = collect(self::cases())
            ->first(function (Country $case) use ($key) {
                return $key === $case->getDisplayName('de')
                    || $key === $case->getDisplayName('en');
            });

        if ($match) {
            return $match;
        }

        return Country::Other;
    }
}
