<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperBlacklistedEmailDomain
 */
class BlacklistedEmailDomain extends Model
{
    protected $fillable = [
        'name',
    ];

    public static function notListed ($domain) {
        return self::where('name', $domain)->count() == 0;
    }

    public static function listed ($domain) {
        return self::where('name', $domain)->count() != 0;
    }
}
