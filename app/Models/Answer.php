<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Answer.
 *
 * @property object $value
 * @mixin IdeHelperAnswer
 */
class Answer extends BaseModel
{
    use HasFactory;

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
