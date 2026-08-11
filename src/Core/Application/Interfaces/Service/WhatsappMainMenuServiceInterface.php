<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Service;

use App\Core\Domain\Entity\MessageEntity;
use App\Core\Enum\WhatsappMenuOption;

interface WhatsappMainMenuServiceInterface
{
    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function show(MessageEntity $message): array;

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function showMunicipalityChoice(MessageEntity $message, string $municipality): array;

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function handleOption(MessageEntity $message, WhatsappMenuOption $menuOption): array;

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function endConversation(MessageEntity $message): array;
}
