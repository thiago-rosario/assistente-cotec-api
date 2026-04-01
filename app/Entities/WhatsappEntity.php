<?php

declare(strict_types=1);

namespace App\Entities;

class WhatsappEntity
{
    protected ?string $messageSid;

    protected ?string $from;

    protected ?string $to;

    protected ?string $body;

    public function __construct(
        ?string $messageSid = null,
        ?string $from = null,
        ?string $to = null,
        ?string $body = null
    ) {
        $this->setMessageSid($messageSid);
        $this->setFrom($from);
        $this->setTo($to);
        $this->setBody($body);
    }

    public function getMessageSid(): ?string
    {
        return $this->messageSid;
    }

    public function setMessageSid(?string $messageSid): self
    {
        $this->messageSid = $this->normalizeString($messageSid);

        return $this;
    }

    public function getFrom(): ?string
    {
        return $this->from;
    }

    public function setFrom(?string $from): self
    {
        $this->from = $this->normalizeAddress($from);

        return $this;
    }

    public function getTo(): ?string
    {
        return $this->to;
    }

    public function setTo(?string $to): self
    {
        $this->to = $this->normalizeAddress($to);

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $this->normalizeString($body);

        return $this;
    }

    /**
     * @return array{
     *     messageSid: ?string,
     *     from: ?string,
     *     to: ?string,
     *     body: ?string
     * }
     */
    public function toBody(): array
    {
        return [
            'messageSid' => $this->messageSid,
            'from' => $this->from,
            'to' => $this->to,
            'body' => $this->body,
        ];
    }

    /**
     * @return array{
     *     messageSid: ?string,
     *     from: ?string,
     *     to: ?string,
     *     body: ?string
     * }
     */
    public function toArray(): array
    {
        return $this->toBody();
    }

    /**
     * @param array{
     *     MessageSid?: mixed,
     *     SmsMessageSid?: mixed,
     *     message_sid?: mixed,
     *     messageSid?: mixed,
     *     From?: mixed,
     *     from?: mixed,
     *     To?: mixed,
     *     to?: mixed,
     *     Body?: mixed,
     *     body?: mixed
     * } $payload
     */
    public static function fromWebhookPayload(array $payload): self
    {
        return new self(
            is_string($payload['MessageSid'] ?? null)
                ? $payload['MessageSid']
                : (is_string($payload['SmsMessageSid'] ?? null)
                    ? $payload['SmsMessageSid']
                    : (is_string($payload['message_sid'] ?? null)
                        ? $payload['message_sid']
                        : ($payload['messageSid'] ?? null))),
            is_string($payload['From'] ?? null) ? $payload['From'] : ($payload['from'] ?? null),
            is_string($payload['To'] ?? null) ? $payload['To'] : ($payload['to'] ?? null),
            is_string($payload['Body'] ?? null) ? $payload['Body'] : ($payload['body'] ?? null),
        );
    }

    protected function normalizeString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalizedValue = trim($value);

        if ($normalizedValue === '') {
            return null;
        }

        return $normalizedValue;
    }

    protected function normalizeAddress(mixed $value): ?string
    {
        $normalizedAddress = $this->normalizeString($value);

        if ($normalizedAddress === null) {
            return null;
        }

        if (str_starts_with($normalizedAddress, 'whatsapp:')) {
            return $normalizedAddress;
        }

        return 'whatsapp:'.$normalizedAddress;
    }
}
