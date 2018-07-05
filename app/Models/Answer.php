<?php

namespace App\Models;

/**
 * Class Answer.
 *
 * @property object $value
 */
class Answer extends BaseModel
{
    protected $fillable = [
        'value',
        'user_id',
        'element_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'user_id',
    ];

    public function getValueAttribute()
    {
        return json_decode($this->attributes['value']);
    }

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = json_encode($value);
    }
}
