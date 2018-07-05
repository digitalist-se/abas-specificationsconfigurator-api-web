<?php

namespace App\Models;

use App\Responsibilities\Uuids;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use Uuids;

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
