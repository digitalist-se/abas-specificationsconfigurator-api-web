<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

class LeadRegisterMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public $leadUser;

    /**
     * Create a new message instance.
     *
     * @param $leadUser
     */
    public function __construct($leadUser)
    {
        $this->leadUser = $leadUser;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->html($this->renderMailMessage())
            ->subject(Lang::get('email.lead.register.subject'));
    }

    protected function renderMailMessage(): string
    {
        // message is imported automatically by recipient. lines should match the usecase
        $columns = collect([
            'first_name',
            'last_name',
            'email',
            'company_name',
            'partner_tracking',
        ]);
        return $columns->map(fn ($column) => $this->user->$column ?? '')
            ->join("\n");
    }
}
