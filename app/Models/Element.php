<?php

namespace App\Models;

use App\Responsibilities\HasIllustrationStates;

class Element extends BaseModel
{
    use HasIllustrationStates;
    protected $fillable = [
        'id',
        'section_id',
        'type',

        'content', // content or question
        'print', // print only. content pre answer
        'sort',

        // choice type values:
        'choice_type_id',

        // slider values:
        'steps',
        'min',
        'max',
        'illustration_states',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'sort',
        'section_id',
    ];

    public function choiceType()
    {
        return $this->belongsTo('App\Models\ChoiceType');
    }
}
