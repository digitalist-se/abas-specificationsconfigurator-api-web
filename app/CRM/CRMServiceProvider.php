<?php

namespace App\CRM;

use App\CRM\Adapter\TrackEventAdapter;
use App\CRM\Service\CRMService;
use App\CRM\Service\HubSpotCRMService;
use App\CRM\Service\NoOpCRMService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class CRMServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->bind(HubSpotCRMService::class, function ($app) {
            return new HubSpotCRMService(Config::get('services.hubSpot'));
        });
        $this->app->bind(SalesforceAuthService::class, function ($app) {
            return new SalesforceAuthService(Config::get('services.salesforce'));
        });
        $this->app->singleton(CRMService::class, function ($app) {
            if (Config::get('services.hubSpot.enabled')) {
                return $app->make(HubSpotCRMService::class);
            }

            return $app->make(NoOpCRMService::class);
        });
    }
}
