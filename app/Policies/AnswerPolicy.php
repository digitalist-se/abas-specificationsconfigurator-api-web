<?php

namespace App\Policies;

use App\Models\Answer;
use App\Models\Role;
use App\Models\User;

class AnswerPolicy
{
    /**
     * Determine if the given answer can be updated by the user.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine if the given answer can be updated by the user.
     *
     * @param \App\Models\User   $user
     * @param \App\Models\Answer $answer
     *
     * @return bool
     */
    public function update(User $user, Answer $answer)
    {
        return $user->role->is(Role::ADMIN) || $user->id === $answer->user_id;
    }

    /**
     * Determine if the given answer can be viewed by the user.
     *
     * @param \App\Models\User   $user
     * @param \App\Models\Answer $answer
     *
     * @return bool
     */
    public function view(User $user, Answer $answer)
    {
        return $user->role->is(Role::ADMIN) || $user->id === $answer->user_id;
    }
}
