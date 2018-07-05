<?php

namespace App\Models;

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
        return $this->hasMany('App\Models\Option')->orderBy('sort');
    }
}
