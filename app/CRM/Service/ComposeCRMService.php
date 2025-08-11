<?php

namespace App\CRM\Service;

use App\Events\ExportedDocument;
use Illuminate\Auth\Events\Registered;

class ComposeCRMService implements CRMService
{
    /**
     * @var CRMService[]
     */
    private array $services;

    /**
     * @param CRMService[] $services
     */
    public function __construct(array $services)
    {
        $this->services = $services;
    }

    public function handleUserRegistered(Registered $event): bool
    {
        $result = true;
        foreach ($this->services as $service) {
            $result = $service->handleUserRegistered($event) && $result;
        }

        return $result;
    }

    public function handleDocumentExport(ExportedDocument $event): bool
    {
        $result = true;
        foreach ($this->services as $service) {
            $result = $service->handleDocumentExport($event) && $result;
        }

        return $result;
    }
}
