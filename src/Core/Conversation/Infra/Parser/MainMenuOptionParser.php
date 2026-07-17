<?php

declare(strict_types=1);

namespace App\Core\Conversation\Infra\Parser;

use App\Core\Conversation\Enum\MainMenuOptionEnum;
use App\Core\Conversation\Application\Interfaces\Parser\MainMenuOptionParserInterface;

class MainMenuOptionParser implements MainMenuOptionParserInterface
{
    public function parse(string $input): ?MainMenuOptionEnum
    {
        return MainMenuOptionEnum::tryFrom(trim($input));
    }
}
