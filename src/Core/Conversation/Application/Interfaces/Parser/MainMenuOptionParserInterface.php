<?php

declare(strict_types=1);

namespace App\Core\Conversation\Application\Interfaces\Parser;

use App\Core\Conversation\Enum\MainMenuOptionEnum;

interface MainMenuOptionParserInterface
{
    public function parse(string $input): ?MainMenuOptionEnum;
}
