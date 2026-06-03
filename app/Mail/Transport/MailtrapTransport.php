<?php

namespace App\Mail\Transport;

use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\RawMessage;

class MailtrapTransport implements TransportInterface
{
    public function __construct(
        private string $apiKey,
        private int $inboxId
    ) {}

    public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
    {
        /** @var \Symfony\Component\Mime\Email $message */
        $email = (new MailtrapEmail())
            ->from(...$message->getFrom())
            ->to(...$message->getTo())
            ->subject($message->getSubject())
            ->html($message->getHtmlBody())
            ->text($message->getTextBody() ?? strip_tags($message->getHtmlBody() ?? ''));

        MailtrapClient::initSendingEmails(
            apiKey: $this->apiKey,
            isSandbox: true,
            inboxId: $this->inboxId
        )->send($email);

        return new SentMessage($message, $envelope ?? Envelope::create($message));
    }

    public function __toString(): string
    {
        return 'mailtrap';
    }
}