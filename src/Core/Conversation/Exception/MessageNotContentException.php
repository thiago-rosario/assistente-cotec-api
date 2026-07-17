<?php

declare(strict_types=1);

namespace App\Core\Conversation\Exception;

use App\Core\Conversation\Enum\ConversationCodeExceptionEnum;
use Throwable;

class MessageNotContentException extends \RuntimeException
{
    public function __construct(
        string $message = 'Payload de mensagem recebido sem conteúdo.',
        int $code = ConversationCodeExceptionEnum::MessageNotContent->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
