<?php

namespace App\Rules;

use App\Models\BlacklistedEmailDomain;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;

class NotBlacklistedDomain implements Rule
{

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $arr = explode('@', strtolower(trim($value)));

        if (count($arr) !== 2) {
            return false;
        }

        return BlacklistedEmailDomain::notListed($arr[1]);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attribute is blacklisted!';
    }
}
