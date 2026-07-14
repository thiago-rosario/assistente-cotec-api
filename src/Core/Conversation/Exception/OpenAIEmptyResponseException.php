<?php

declare(strict_types=1);

namespace App\Core\Conversation\Exception;

use App\Core\Conversation\Enum\CodeExceptionEnum;
use Throwable;

class OpenAIEmptyResponseException extends \RuntimeException
{
    public function __construct(
        string $message = 'A OpenAI não retornou conteúdo para o prompt informado.',
        int $code = CodeExceptionEnum::OpenAIEmptyResponse->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
