<?php

namespace App\Rules;

use App\Models\Locale;
use Illuminate\Contracts\Validation\Rule;

class IsSupportedLocale implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!is_string($value)) {
            return false;
        }

        return Locale::supportedSet()->contains($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attribute is not a supported locale!';
    }
}
