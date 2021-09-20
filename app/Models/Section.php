<?php

namespace App\Models;

use App\Responsibilities\HasIllustrationStates;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @mixin IdeHelperSection
 */
class Section extends BaseModel
{
    use HasIllustrationStates;
    use HasFactory;

    protected $fillable = [
        'slug_name',
        'headline',
        'description',
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
        return $this->elements();
    }
}
