<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountLocked extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $token;

    /**
     * Create a new message instance.
     */
    public function __construct($user, string $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $unlockUrl = route('account.unlock', ['token' => $this->token]);

        return $this->subject('Akun Anda Dikunci — Instruksi Unlock')
            ->view('emails.account_locked')
            ->with([
                'name' => $this->user->name,
                // 'email' => $this->user->email,
                'unlockUrl' => $unlockUrl,
            ]);
    }
}