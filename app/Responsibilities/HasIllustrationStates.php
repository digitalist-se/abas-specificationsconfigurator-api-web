<?php

namespace App\Responsibilities;

trait HasIllustrationStates
{
    public function getIllustrationStatesAttribute()
    {
        return json_decode($this->attributes['illustration_states']);
    }

    public function setIllustrationStatesAttribute($value)
    {
        $this->attributes['illustration_states'] = $value ? json_encode($value) : null;
    }
}
