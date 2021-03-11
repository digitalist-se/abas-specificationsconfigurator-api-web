<?php

namespace App\Responsibilities;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

trait Uuids
{
    // You would usually specify $incrementing = false for uuid models. However, this is a trait and this wouldn't work.
    // So we override the getter, which is used throughout laravel's code.
    // public $incrementing = false;

    /**
     * Always returns false.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * Boot function from laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }
}
