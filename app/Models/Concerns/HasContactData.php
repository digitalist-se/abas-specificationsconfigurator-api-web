<?php

namespace App\Models\Concerns;

use App\Enums\ContactType;

/**
 * @mixin \App\Models\User
 */
trait HasContactData
{
    public function getContactEmail(ContactType $type): ?string
    {
        return match ($type) {
            ContactType::User    => $this->email,
            ContactType::Company => $this->contact_email,
        };
    }

    public function getContactFirstName(ContactType $type): ?string
    {
        return match ($type) {
            ContactType::User    => $this->first_name,
            ContactType::Company => $this->contact_first_name,
        };
    }

    public function getContactLastName(ContactType $type): ?string
    {
        return match ($type) {
            ContactType::User    => $this->last_name,
            ContactType::Company => $this->contact_last_name,
        };
    }

    public function getContactCompany(ContactType $type): ?string
    {
        return match ($type) {
            ContactType::User    => $this->user_company,
            ContactType::Company => $this->company_name,
        };
    }

    public function getContactSalutation(ContactType $type): ?string
    {
        return match ($type) {
            ContactType::User    => null,
            ContactType::Company => $this->salutation,
        };
    }

    public function getContactFunction(ContactType $type): ?string
    {
        return match ($type) {
            ContactType::User    => null,
            ContactType::Company => $this->contact_function,
        };
    }
}
