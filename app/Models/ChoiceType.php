<?php

namespace App\Models;

/**
 * @mixin IdeHelperChoiceType
 */
class ChoiceType extends BaseModel
{
    protected $fillable = [
        'id',
        'type',
        'multiple',
        'tiles',
    ];

    protected $casts = [
        'multiple' => 'boolean',
        'tiles'    => 'boolean',
    ];

    public function options()
    {
        return $this->hasMany(\App\Models\Option::class)->orderBy('sort');
    }
}
