<?php

namespace App\CRM\Adapter;

use App\Models\User;

class UserNoteAdapter
{
    public function createNote(User $user): string
    {
        $columns = collect([
            'salutation',
            'contact_first_name',
            'contact_last_name',
            'email',
            'contact_function',
            'phone',
        ]);

        return __('New specification configuration:')
            ."\n\n".
            $columns->map(fn ($column) => __('note.attributes.'.$column).' '.$user->$column ?? '')
                ->join("\n");
    }
}
