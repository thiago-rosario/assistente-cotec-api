<?php

declare(strict_types=1);

namespace App\Core\Identity\Domain\ValueObject;

use App\Core\Identity\Domain\Trait\MethodsMagicsTrait;
use App\Core\Identity\Domain\Validation\TemporaryAuthorizationDomainValidation;

/**
 * @property-read string $whatsappNumber
 * @property-read string $conversationId
 */
final class AuthorizationContext
{
    use MethodsMagicsTrait;

    protected readonly string $whatsappNumber;

    protected readonly string $conversationId;

    public function __construct(string $whatsappNumber, string $conversationId)
    {
        $this->whatsappNumber = trim($whatsappNumber);
        $this->conversationId = trim($conversationId);

        TemporaryAuthorizationDomainValidation::validateWhatsappNumber($this->whatsappNumber);
        TemporaryAuthorizationDomainValidation::validateConversationId($this->conversationId);
    }

    public static function forWhatsappConversation(string $whatsappNumber, string $conversationId): self
    {
        return new self(
            whatsappNumber: $whatsappNumber,
            conversationId: $conversationId,
        );
    }

    public function equals(self $context): bool
    {
        return $this->whatsappNumber === $context->whatsappNumber
            && $this->conversationId === $context->conversationId;
    }

    /**
     * @return array{whatsapp_number: string, conversation_id: string}
     */
    public function toArray(): array
    {
        return [
            'whatsapp_number' => $this->whatsappNumber,
            'conversation_id' => $this->conversationId,
        ];
    }
}
