<?php

namespace App\Models;

use App\Responsibilities\HasIllustrationStates;

/**
 * @mixin IdeHelperSection
 */
class Section extends BaseModel
{
    use HasIllustrationStates;
    protected $fillable = [
        'slug_name',
        'headline',
        'description',
        'print_description',
        'sort',
        'chapter_id',
        'has_headline',
        'illustration_states',
        ];

    protected $casts = [
        'has_headline' => 'boolean',
    ];

    public function elements()
    {
        return $this->hasMany('App\Models\Element')->orderBy('sort');
    }

    public function printableElements()
    {
        return $this->elements();
    }

    public function appElements()
    {
        return $this->hasMany('App\Models\Element')->orderBy('sort')->where('type', '<>', 'print_headline');
    }
}
