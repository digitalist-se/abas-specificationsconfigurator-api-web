<?php

namespace App\Models;

use App\Responsibilities\HasIllustrationStates;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Element.
 *
 * @mixin IdeHelperElement
 */
class Element extends BaseModel
{
    use HasIllustrationStates;
    use HasFactory;

    const DOCUMENT_COLUMN_OFFSET = 'C';

    protected $fillable = [
        'id',
        'section_id',
        'type',

        'content', // content or question
        'sub_content', // additional content of question
        'sort',

        // choice type values:
        'choice_type_id',

        // slider values:
        'steps',
        'min',
        'max',
        'illustration_states',
        'document_row',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'sort',
        'section_id',
    ];

    public function choiceType()
    {
        return $this->belongsTo(\App\Models\ChoiceType::class);
    }

    public function getDocumentCellAttribute()
    {
        return self::DOCUMENT_COLUMN_OFFSET.$this->document_row;
    }
}
