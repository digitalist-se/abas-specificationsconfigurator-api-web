<?php

namespace App\Models;

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
