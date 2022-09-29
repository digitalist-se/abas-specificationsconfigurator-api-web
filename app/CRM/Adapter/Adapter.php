<?php

namespace App\CRM\Adapter;

use App\Models\User;

interface Adapter
{
    public function toCreateRequestBody(User $user): array;
}
