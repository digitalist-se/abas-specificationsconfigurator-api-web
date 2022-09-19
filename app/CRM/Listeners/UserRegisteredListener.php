<?php

namespace App\CRM\Listeners;

use App\CRM\Facades\CRM;
use App\Enums\ContactType;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

class UserRegisteredListener
{
    public function handle(Registered $event)
    {
        $user = $event->user;
        if (! $user instanceof User) {
            return;
        }

        CRM::upsertContact($user, ContactType::User, ['erp_registration_trigger' => true]);
        CRM::trackUserRegistered($event);
    }
}
