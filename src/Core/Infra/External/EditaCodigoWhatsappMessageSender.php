<?php

declare(strict_types=1);

namespace App\Core\Infra\External;

use App\Core\Application\Interfaces\Service\WhatsappMessageSenderInterface;
use App\Core\Application\Support\WhatsappLogContext;
use App\Core\Exception\EditaCodigoWhatsappMessageSenderException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class EditaCodigoWhatsappMessageSender implements WhatsappMessageSenderInterface
{
    public function send(
        string $phone,
        string $message,
        ?string $externalId = null,
    ): void {
        $url = $this->webhookUrl();

        if ($url === '') {
            throw new EditaCodigoWhatsappMessageSenderException(
                message: 'Endpoint local da EditaCódigo não configurado.',
            );
        }

        try {
            $request = Http::timeout($this->timeout())
                ->connectTimeout($this->connectTimeout())
                ->retry(
                    $this->retryTimes(),
                    fn (int $attempt): int => $this->retryDelay($attempt),
                    fn (Throwable $throwable): bool => $this->shouldRetry($throwable, $url, $phone, $externalId),
                )
                ->acceptJson()
                ->asJson();

            if (! $this->verifyTls()) {
                $request->withoutVerifying();
            }

            $response = $request
                ->throw()
                ->post($url, $this->payload($phone, $message, $externalId));
        } catch (Throwable $throwable) {
            throw new EditaCodigoWhatsappMessageSenderException(
                status: $this->statusFromThrowable($throwable),
                url: $url,
                previous: $throwable,
            );
        }

        Log::info('whatsapp_reply_sent', [
            ...WhatsappLogContext::message($externalId, $phone, null),
            'id_msg' => $externalId,
            'reply_length' => mb_strlen($message),
            'http_status' => $response->status(),
        ]);
    }

    /**
     * @return array{
     *     usuario: string,
     *     token: string,
     *     action: string,
     *     message: array{telefone: string, msg: string, id_msg: string|null}
     * }
     */
    private function payload(string $phone, string $message, ?string $externalId): array
    {
        return [
            'usuario' => (string) config('services.editacodigo_bot.user', ''),
            'token' => (string) config('services.editacodigo_bot.token', ''),
            'action' => 'EnviarMsg',
            'message' => [
                'telefone' => $phone,
                'msg' => $message,
                'id_msg' => $externalId,
            ],
        ];
    }

    private function webhookUrl(): string
    {
        return trim((string) config('services.editacodigo_bot.webhook_url', ''));
    }

    private function timeout(): int
    {
        return max(1, (int) config('services.editacodigo_bot.timeout', 15));
    }

    private function connectTimeout(): int
    {
        return min(
            $this->timeout(),
            max(1, (int) config('services.editacodigo_bot.connect_timeout', 3)),
        );
    }

    private function retryTimes(): int
    {
        return max(1, (int) config('services.editacodigo_bot.retry_times', 3));
    }

    private function verifyTls(): bool
    {
        return filter_var(config('services.editacodigo_bot.verify_tls', true), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true;
    }

    private function retryDelay(int $attempt): int
    {
        $delay = max(1, (int) config('services.editacodigo_bot.retry_delay_ms', 250));
        $maxDelay = max($delay, (int) config('services.editacodigo_bot.retry_max_delay_ms', 2000));

        return min($maxDelay, $delay * (2 ** max(0, $attempt - 1)));
    }

    private function shouldRetry(Throwable $throwable, string $url, string $phone, ?string $externalId): bool
    {
        $shouldRetry = false;

        if ($throwable instanceof ConnectionException) {
            $shouldRetry = true;
        } elseif ($throwable instanceof RequestException) {
            $shouldRetry = $throwable->response->serverError();
        }

        if ($shouldRetry) {
            Log::warning('whatsapp_reply_retrying', [
                ...WhatsappLogContext::message($externalId, $phone, null),
                'id_msg' => $externalId,
                'url' => $url,
                'http_status' => $this->statusFromThrowable($throwable),
                'exception' => $throwable::class,
                'exception_message' => $throwable->getMessage(),
            ]);
        }

        return $shouldRetry;
    }

    private function statusFromThrowable(Throwable $throwable): ?int
    {
        if (! $throwable instanceof RequestException) {
            return null;
        }

        return $throwable->response->status();
    }
}
