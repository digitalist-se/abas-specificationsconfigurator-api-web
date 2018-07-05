<?php

namespace App\Models;

use App\Responsibilities\HasIllustrationStates;

class Chapter extends BaseModel
{
    use HasIllustrationStates;
    protected $fillable = [
        'name',
        'print_name',
        'slug_name',
        'sort',
        'description',
        'print_description',
        'illustration_states',
    ];

    protected $attributes = [
        'visible' => true,
    ];

    public function sections()
    {
        return $this->hasMany('App\Models\Section')->orderBy('sort');
    }
}
