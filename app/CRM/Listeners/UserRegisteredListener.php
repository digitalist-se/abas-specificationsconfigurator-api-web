<?php

namespace App\CRM\Listeners;

use App\CRM\Facades\CRM;
use Illuminate\Auth\Events\Registered;

class UserRegisteredListener
{
    public function handle(Registered $event)
    {
        CRM::handleUserRegistered($event);
    }
}
