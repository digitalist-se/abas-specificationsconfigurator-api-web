<?php

namespace App\Events;

use App\Http\Resources\SpecificationDocument;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExportedDocument
{
    use Dispatchable, SerializesModels;

    public function __construct(public User $user, public SpecificationDocument $document)
    {
    }
}
