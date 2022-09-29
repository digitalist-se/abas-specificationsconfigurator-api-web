<?php

namespace App\Models;

/**
 * @mixin IdeHelperOption
 */
class Option extends BaseModel
{
    protected $fillable = [
        'id',
        'sort',
        'type',
        'text',
        'value',
        'icon',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'sort',
    ];

    public function choiceType()
    {
        $this->belongsTo(\App\Models\ChoiceType::class);
    }
}
