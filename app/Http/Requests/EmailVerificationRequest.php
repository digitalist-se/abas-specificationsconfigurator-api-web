<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest as BaseEmailVerificationRequest;

class EmailVerificationRequest extends BaseEmailVerificationRequest
{
    public function authorize()
    {
        $user = User::find($this->route('id'));

        if (! $user) {
            return false;
        }

        if (! hash_equals((string) $this->route('hash'),
            sha1($user->getEmailForVerification()))) {
            return false;
        }

        $this->setUserResolver(function () use ($user) {
            return $user;
        });

        return true;
    }
}
