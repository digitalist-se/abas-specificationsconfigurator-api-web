<?php

namespace App\CRM\Service\Auth;

interface AuthTokenProviderInterface
{
    public function provide(): string;

    public function refresh(): void;

    public function tokenType(): string;
}
