<?php

declare(strict_types=1);

namespace App\Entities;

use RuntimeException;

class TwilioEntity
{
    protected ?string $twilioAccountSid;

    protected ?string $twilioAuthToken;

    protected ?string $twilioWhatsappFrom;

    public bool $validateSignature;

    public function __construct(
        ?string $accountSid = null,
        ?string $authToken = null,
        ?string $whatsappFrom = null,
        public ?string $statusCallback = null,
        ?bool $validateSignature = null
    ) {
        $this->twilioAccountSid = $this->normalizeConfigValue($accountSid) ?? $this->normalizeConfigValue(config('twilio.account_sid'));
        $this->twilioAuthToken = $this->normalizeConfigValue($authToken) ?? $this->normalizeConfigValue(config('twilio.auth_token'));
        $this->twilioWhatsappFrom = $this->normalizeConfigValue($whatsappFrom) ?? $this->normalizeConfigValue(config('twilio.whatsapp_from'));
        $this->statusCallback ??= $this->normalizeConfigValue(config('twilio.status_callback'));
        $this->validateSignature = $validateSignature ?? (bool) config('twilio.validate_signature', false);
    }

    public function getTwilioAccountSid(): ?string
    {
        return $this->twilioAccountSid;
    }

    public function setTwilioAccountSid(string $twilioAccountSid): self
    {
        $this->twilioAccountSid = $this->normalizeConfigValue($twilioAccountSid);

        return $this;
    }

    public function getTwilioAuthToken(): ?string
    {
        return $this->twilioAuthToken;
    }

    public function setTwilioAuthToken(string $twilioAuthToken): self
    {
        $this->twilioAuthToken = $this->normalizeConfigValue($twilioAuthToken);

        return $this;
    }

    public function getTwilioWhatsappFrom(): ?string
    {
        return $this->twilioWhatsappFrom;
    }

    public function setTwilioWhatsappFrom(string $twilioWhatsappFrom): self
    {
        $this->twilioWhatsappFrom = $this->normalizeConfigValue($twilioWhatsappFrom);

        return $this;
    }

    /**
     * Garante que a integração tenha credenciais mínimas antes de chamar a Twilio.
     */
    public function assertConfigured(): void
    {
        $missingConfiguration = [];

        if ($this->twilioAccountSid === null) {
            $missingConfiguration[] = 'TWILIO_ACCOUNT_SID';
        }

        if ($this->twilioAuthToken === null) {
            $missingConfiguration[] = 'TWILIO_AUTH_TOKEN';
        }

        if ($this->twilioWhatsappFrom === null) {
            $missingConfiguration[] = 'TWILIO_WHATSAPP_FROM';
        }

        if ($missingConfiguration !== []) {
            throw new RuntimeException(
                'Twilio configuration is missing: '.implode(', ', $missingConfiguration)
            );
        }
    }

    /**
     * Normaliza números para o formato exigido pelo canal WhatsApp da Twilio.
     */
    public function formatWhatsAppAddress(string $address): string
    {
        $normalizedAddress = trim($address);

        if (str_starts_with($normalizedAddress, 'whatsapp:')) {
            return $normalizedAddress;
        }

        return 'whatsapp:'.$normalizedAddress;
    }

    /**
     * Converte valores de configuração vazios em `null` para simplificar validações.
     */
    protected function normalizeConfigValue(mixed $value): ?string
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

    /**
     * @param array{
     *     AccountSid?: mixed,
     *     account_sid?: mixed,
     *     AuthToken?: mixed,
     *     auth_token?: mixed,
     *     From?: mixed,
     *     from?: mixed,
     *     whatsapp_from?: mixed,
     *     StatusCallback?: mixed,
     *     status_callback?: mixed,
     *     validate_signature?: mixed
     * } $payload
     */
    public static function fromWebhookPayload(array $payload): self
    {
        return new self(
            is_string($payload['AccountSid'] ?? null) ? $payload['AccountSid'] : ($payload['account_sid'] ?? null),
            is_string($payload['AuthToken'] ?? null) ? $payload['AuthToken'] : ($payload['auth_token'] ?? null),
            is_string($payload['From'] ?? null) ? $payload['From'] : ($payload['from'] ?? ($payload['whatsapp_from'] ?? null)),
            is_string($payload['StatusCallback'] ?? null) ? $payload['StatusCallback'] : ($payload['status_callback'] ?? null),
            (bool) ($payload['validate_signature'] ?? false)
        );
    }
}
