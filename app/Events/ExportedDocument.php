<?php

namespace App\Events;

use App\Http\Resources\SpecificationDocument;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExportedDocument
{
    use Dispatchable, SerializesModels;

    public User $user;
    public SpecificationDocument $document;

    /**
     * @param \App\Models\User                          $user
     * @param \App\Http\Resources\SpecificationDocument $document
     */
    public function __construct(User $user, SpecificationDocument $document)
    {
        $this->user = $user;
        $this->document = $document;
    }
}
