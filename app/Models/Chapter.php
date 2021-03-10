<?php

namespace App\Models;

use App\Responsibilities\HasIllustrationStates;

/**
 * Class Chapter
 *
 * @package App\Models
 * @property int $worksheet
 * @mixin IdeHelperChapter
 */
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
        'worksheet'
    ];

    protected $attributes = [
        'visible' => true,
    ];

    public function sections()
    {
        return $this->hasMany('App\Models\Section')->orderBy('sort');
    }
}
