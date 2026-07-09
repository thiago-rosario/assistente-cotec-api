<?php

declare(strict_types=1);

namespace App\Core\Infra\Log;

use App\Core\Application\Interfaces\Log\WhatsappBotLoggerInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsappBotLogger implements WhatsappBotLoggerInterface
{
    private const string CHANNEL = 'whatsapp_bot';

    /**
     * Campos permitidos no contexto do log.
     *
     * @var array<int, string>
     */
    private const array ALLOWED_CONTEXT_KEYS = [
        'external_id',
        'sender',
        'source',
        'intent',
        'filters',
        'result_total',
        'reply_length',
        'duration_ms',
        'total_duration_ms',
        'ia_duration_ms',
        'search_duration_ms',
        'reply_duration_ms',
        'error_class',
        'error_message',
        'reason',
        'idle_cycles',
        'exit_code',
    ];

    public function botStarted(array $context = []): void
    {
        $this->info('bot_started', $context);
    }

    public function messageDetected(array $context = []): void
    {
        $this->info('message_detected', $context);
    }

    public function messageIgnored(array $context = []): void
    {
        $this->warning('message_ignored', $context);
    }

    public function messageProcessingStarted(array $context = []): void
    {
        $this->info('message_processing_started', $context);
    }

    public function messageInterpreted(array $context = []): void
    {
        $this->info('message_interpreted', $context);
    }

    public function searchFinished(array $context = []): void
    {
        $this->info('search_finished', $context);
    }

    public function replySent(array $context = []): void
    {
        $this->info('reply_sent', $context);
    }

    public function replySkipped(array $context = []): void
    {
        $this->warning('reply_skipped', $context);
    }

    public function idleCycles(array $context = []): void
    {
        $this->debug('bot_idle_cycles', $context);
    }

    public function botError(Throwable $exception, array $context = []): void
    {
        $this->error('bot_error', array_merge($context, [
            'error_class' => $exception::class,
            'error_message' => $exception->getMessage(),
        ]));
    }

    public function botCritical(Throwable $exception, array $context = []): void
    {
        $this->critical('bot_critical', array_merge($context, [
            'error_class' => $exception::class,
            'error_message' => $exception->getMessage(),
        ]));
    }

    private function debug(string $event, array $context = []): void
    {
        Log::channel(self::CHANNEL)->debug($event, $this->sanitizeContext($context));
    }

    private function info(string $event, array $context = []): void
    {
        Log::channel(self::CHANNEL)->info($event, $this->sanitizeContext($context));
    }

    private function warning(string $event, array $context = []): void
    {
        Log::channel(self::CHANNEL)->warning($event, $this->sanitizeContext($context));
    }

    private function error(string $event, array $context = []): void
    {
        Log::channel(self::CHANNEL)->error($event, $this->sanitizeContext($context));
    }

    private function critical(string $event, array $context = []): void
    {
        Log::channel(self::CHANNEL)->critical($event, $this->sanitizeContext($context));
    }

    /**
     * Remove payload pesado e evita vazar mensagem/resposta completa no log.
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function sanitizeContext(array $context): array
    {
        $safeContext = [];

        foreach (self::ALLOWED_CONTEXT_KEYS as $key) {
            if (! array_key_exists($key, $context)) {
                continue;
            }

            $safeContext[$key] = $this->sanitizeValue($context[$key]);
        }

        return array_filter(
            $safeContext,
            static fn (mixed $value): bool => $value !== null && $value !== ''
        );
    }

    private function sanitizeValue(mixed $value): mixed
    {
        if (is_string($value)) {
            return mb_substr($value, 0, 300);
        }

        if (is_int($value) || is_float($value) || is_bool($value) || $value === null) {
            return $value;
        }

        if (is_array($value)) {
            return array_map(
                fn (mixed $item): mixed => $this->sanitizeValue($item),
                array_slice($value, 0, 20, true)
            );
        }

        return '[unsupported_value]';
    }
}
