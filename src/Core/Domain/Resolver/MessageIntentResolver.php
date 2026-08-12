<?php

declare(strict_types=1);

namespace App\Core\Domain\Resolver;

use App\Core\Application\Interfaces\Service\MessageIntentResolverInterface;
use App\Core\Domain\Entity\MessageEntity;
use App\Core\Enum\WhatsappMenuOption;

class MessageIntentResolver implements MessageIntentResolverInterface
{
    public function menuOption(MessageEntity $message): ?WhatsappMenuOption
    {
        if (preg_match('/^\s*([0-3])(?:\D|$)/', $message->content(), $matches) === 1) {
            return WhatsappMenuOption::from($matches[1]);
        }

        if (preg_match('/^\s*\d+\s*$/', $message->content()) === 1) {
            return WhatsappMenuOption::Invalid;
        }

        return null;
    }

    public function isMainMenuRequest(MessageEntity $message): bool
    {
        return in_array($message->normalizedContent(), [
            'ajuda',
            'cancelar',
            'inicio',
            'iniciar',
            'menu',
            'opcoes',
            'voltar',
        ], true);
    }
}
