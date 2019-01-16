<?php

namespace App\Models;

use App\Responsibilities\HasIllustrationStates;

/**
 * Class Element
 * @package App\Models
 * @property string $document_cell
 * @property integer $document_row
 */
class Element extends BaseModel
{
    use HasIllustrationStates;
    const DOCUMENT_COLUMN_OFFSET = 'C';
    protected $fillable = [
        'id',
        'section_id',
        'type',

        'content', // content or question
        'sub_content', // additional content of question
        'print', // print only. content pre answer
        'sort',

        // choice type values:
        'choice_type_id',

        // slider values:
        'steps',
        'min',
        'max',
        'illustration_states',
        'document_row'
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

    public function getDocumentCellAttribute()
    {
        return self::DOCUMENT_COLUMN_OFFSET.$this->document_row;
    }
}
