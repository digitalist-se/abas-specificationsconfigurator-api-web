<?php

namespace App\Models;

/**
 * @mixin IdeHelperText
 */
class Text extends BaseModel
{
    protected $fillable = [
        'key',
        'locale',
        'value',
        'description',
        'public',
    ];

    protected $attributes = [
        'locale'      => 'de',
        'description' => '',
        'public'      => true, // public to api is default
    ];

    protected $casts = [
        'public' => 'boolean',
    ];
}
