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

    public function getDisplayName(): string
    {
        if ($this === self::Other) {
            return __('Other');
        }

        $countryLocale = '-'.Str::upper($this->value);

        return \Locale::getDisplayRegion($countryLocale, app()->getLocale());
    }
}
