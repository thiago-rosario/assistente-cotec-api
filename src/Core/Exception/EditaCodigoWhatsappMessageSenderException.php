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
     * @return array{
     *     status: int|null,
     *     url: string|null,
     *     previous_exception: class-string<Throwable>|null,
     *     previous_message: string|null
     * }
     */
    public function context(): array
    {
        $previous = $this->getPrevious();

        return [
            'status' => $this->status,
            'url' => $this->sanitizedUrl(),
            'previous_exception' => $previous === null ? null : $previous::class,
            'previous_message' => $previous?->getMessage(),
        ];
    }

    private function sanitizedUrl(): ?string
    {
        if ($this->url === null) {
            return null;
        }

        $parts = parse_url($this->url);

        if ($parts === false || ! isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        $url = $parts['scheme'].'://'.$parts['host'];

        if (isset($parts['port'])) {
            $url .= ':'.$parts['port'];
        }

        return $url.($parts['path'] ?? '');
    }
}
