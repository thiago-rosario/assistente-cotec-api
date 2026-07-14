<?php

declare(strict_types=1);

namespace App\Core\Conversation\Application\Interfaces\Parser;

interface PythonMessageOutputParserInterface
{
    /**
     * @return list<array<string, mixed>>
     */
    public function parse(string $output): array;
}
