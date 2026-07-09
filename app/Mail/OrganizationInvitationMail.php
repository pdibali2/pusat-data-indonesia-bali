<?php

namespace App\Mail;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrganizationInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Organization $organization, public string $email, public string $token)
    {
    }

    public function build(): self
    {
        return $this->subject('Undangan bergabung ke organisasi ' . $this->organization->name)
            ->view('emails.organizations.invitation')
            ->with([
                'organization' => $this->organization,
                'email' => $this->email,
                'token' => $this->token,
            ]);
    }
}
