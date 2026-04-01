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
        $this->twilioWhatsappFrom = $this->normalizeAddress($whatsappFrom) ?? $this->normalizeAddress(config('twilio.whatsapp_from'));
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
        $this->twilioWhatsappFrom = $this->normalizeAddress($twilioWhatsappFrom);

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

    public function formatWhatsAppAddress(string $address): string
    {
        $normalizedAddress = trim($address);

        if (str_starts_with($normalizedAddress, 'whatsapp:')) {
            return $normalizedAddress;
        }

        return 'whatsapp:'.$normalizedAddress;
    }

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

    protected function normalizeAddress(mixed $value): ?string
    {
        $normalized = $this->normalizeConfigValue($value);

        if ($normalized === null) {
            return null;
        }

        return $this->formatWhatsAppAddress($normalized);
    }
}
