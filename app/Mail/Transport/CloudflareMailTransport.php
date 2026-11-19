<?php

namespace App\Mail\Transport;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class CloudflareMailTransport extends AbstractTransport
{
    public function __construct(
        protected string $endpoint,
        protected string $secret
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $recipients = collect($email->getTo())->map(fn ($addr) => $addr->getAddress())->implode(',');
        $subject = $email->getSubject() ?? 'Notificación de Planazo';
        $html = $email->getHtmlBody();
        $text = $email->getTextBody();

        // Si no hay texto plano pero hay HTML, generar fallback
        if (empty($text) && ! empty($html)) {
            $text = strip_tags((string) $html);
        }

        $response = Http::withToken($this->secret)
            ->timeout(15)
            ->post($this->endpoint, [
                'to' => $recipients,
                'subject' => $subject,
                'html' => $html,
                'text' => $text,
                'from_name' => config('mail.from.name', 'Planazo'),
            ]);

        if (! $response->successful() || ! ($response->json('success') ?? false)) {
            $err = $response->json('error') ?? $response->body();
            throw new \RuntimeException("Cloudflare Mail Bridge error: {$err}");
        }
    }

    public function __toString(): string
    {
        return 'cloudflare_bridge';
    }
}
