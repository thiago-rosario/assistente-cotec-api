<?php

declare(strict_types=1);

namespace App\Core\Conversation\Application\Interfaces\Parser;

interface PythonBridgeEventParserInterface
{
    /**
     * @return array<string, mixed>|null
     */
    public function parse(string $line): ?array;
}
