<?php


namespace App\Listeners;


use App\Models\User;
use App\Notifications\Register;
use Illuminate\Auth\Events\Registered;

class SendRegisteredNotification
{

    /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Events\Registered  $event
     * @return void
     */
    public function handle(Registered $event)
    {
        if ($event->user instanceof User) {
            $event->user->notify(new Register($event->user));
        }
    }
}
