<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @mixin IdeHelperText
 */
class Text extends BaseModel
{
    use HasFactory;

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
