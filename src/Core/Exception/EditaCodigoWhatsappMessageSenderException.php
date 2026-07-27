<?php

declare(strict_types=1);

namespace App\Core\Exception;

use RuntimeException;
use Throwable;

class EditaCodigoWhatsappMessageSenderException extends RuntimeException
{
    public function __construct(
        string $message = 'Falha ao enviar mensagem pela EditaCódigo.',
        private readonly ?int $status = null,
        private readonly ?string $url = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * @return array{status: int|null, url: string|null}
     */
    public function context(): array
    {
        return [
            'status' => $this->status,
            'url' => $this->url,
        ];
    }
}
