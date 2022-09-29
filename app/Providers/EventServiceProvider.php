<?php

namespace App\Providers;

use App\CRM\Listeners\TrackDocumentExport;
use App\CRM\Listeners\UserRegisteredListener;
use App\Events\ExportedDocument;
use App\Listeners\SendLeadOfDocumentExport;
use App\Listeners\SendLeadOfRegistrationNotification;
use App\Listeners\SendRegisteredNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendRegisteredNotification::class,
            SendLeadOfRegistrationNotification::class,
            UserRegisteredListener::class,
        ],
        ExportedDocument::class => [
            SendLeadOfDocumentExport::class,
            TrackDocumentExport::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
