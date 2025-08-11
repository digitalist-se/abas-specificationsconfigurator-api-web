<?php

namespace App\CRM\Adapter\Hubspot;

use App\Models\User;

class UserNoteAdapter
{
    public function createNoteBody(User $user): string
    {
        $columns = collect([
            'salutation',
            'contact_first_name',
            'contact_last_name',
            'contact_email',
            'contact_function',
            'phone',
        ]);

        $lineSeparator = '<br/>';

        return __('New specification configuration:')
            .$lineSeparator.$lineSeparator.
            $columns->map(fn ($column) => __('note.attributes.'.$column).' '.$user->$column ?? '')
                ->join($lineSeparator);
    }
}
