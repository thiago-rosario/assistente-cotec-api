<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Service;

interface CoreWhatsappResponseFormatterInterface
{
    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    public function mainMenu(): array;

    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    public function municipalityDisambiguation(string $municipality): array;

    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    public function invalidMainMenuOption(): array;

    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    public function unsupportedMessageContent(): array;
}
