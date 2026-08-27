<?php

declare(strict_types=1);

namespace App\Core\Infra\Service;

use App\Core\Application\Interfaces\Service\CoreWhatsappResponseFormatterInterface;
use App\Core\Infra\Message\WhatsappCoreDefaultReplies;
use App\Core\Infra\Message\WhatsappCoreResponsePayloadFactory;

class WhatsappCoreResponseFormatter implements CoreWhatsappResponseFormatterInterface
{
    public function __construct(
        private readonly WhatsappCoreDefaultReplies $defaultReplies,
        private readonly WhatsappCoreResponsePayloadFactory $payloadFactory,
    ) {}

    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    public function mainMenu(): array
    {
        return $this->payloadFactory->empty('main_menu', $this->defaultReplies->mainMenu());
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    public function municipalityDisambiguation(string $municipality): array
    {
        return $this->payloadFactory->empty(
            'municipality_disambiguation',
            $this->defaultReplies->municipalityDisambiguation($municipality),
            ['municipality' => $municipality],
        );
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    public function invalidMainMenuOption(): array
    {
        return $this->payloadFactory->empty('invalid_main_menu_option', $this->defaultReplies->invalidMainMenuOption());
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    public function postQueryAction(): array
    {
        return $this->payloadFactory->empty('post_query_action', $this->defaultReplies->postQueryAction());
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    public function invalidPostQueryAction(): array
    {
        return $this->payloadFactory->empty(
            'invalid_post_query_action',
            $this->defaultReplies->invalidPostQueryAction(),
        );
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    public function conversationClosed(): array
    {
        return $this->payloadFactory->empty(
            'conversation_closed',
            $this->defaultReplies->conversationClosed(),
        );
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    public function unsupportedMessageContent(): array
    {
        return $this->payloadFactory->empty(
            'unsupported_message_content',
            $this->defaultReplies->unsupportedMessageContent(),
        );
    }
}
