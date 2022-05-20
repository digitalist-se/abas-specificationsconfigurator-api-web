<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

class DocumentGeneratedMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public $user;

    /**
     * Create a new message instance.
     *
     * @param $user
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->html($this->renderMailMessage())
            ->subject(Lang::get('email.specification.subject'));
    }

    protected function renderMailMessage(): string
    {
        // message is imported automatically by recipient. lines should match the usecase
        $columns = collect([
            'first_name',
            'last_name',
            'email',
            'company_name',
            'website',
            'zipcode',
            'city',
            'full_street',
            'country',
            'salutation',
            'contact_first_name',
            'contact_last_name',
            'contact_function',
            'phone',
        ]);
        return $columns->map(fn ($column) => $this->user->$column ?? '')
            ->join("\n");
    }
}
