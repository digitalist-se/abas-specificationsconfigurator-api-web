<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\Text;
use App\Models\User;

class TextPolicy
{
    /**
     * Determine if the given post can be updated by the user.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function create(User $user)
    {
        return $user->role->is(Role::ADMIN);
    }

    /**
     * Determine if the given post can be updated by the user.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Text $text
     *
     * @return bool
     */
    public function update(User $user, Text  $text)
    {
        return $user->role->is(Role::ADMIN);
    }
}
