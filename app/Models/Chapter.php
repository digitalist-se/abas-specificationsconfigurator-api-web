<?php

namespace App\Models;

use App\Responsibilities\HasIllustrationStates;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Chapter.
 *
 * @property int $worksheet
 * @mixin IdeHelperChapter
 */
class Chapter extends BaseModel
{
    use HasIllustrationStates;
    use HasFactory;

    protected $fillable = [
        'name',
        'slug_name',
        'sort',
        'description',
        'illustration_states',
        'worksheet',
    ];

    protected $attributes = [
        'visible' => true,
    ];

    public function sections()
    {
        return $this->hasMany(\App\Models\Section::class)->orderBy('sort');
    }
}
