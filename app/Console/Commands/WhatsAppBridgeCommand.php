<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Interfaces\Log\WhatsappBotLoggerInterface;
use App\Core\Application\Interfaces\Usecase\ProcessWhatsappMessageUsecaseInterface;
use App\Core\Enum\WhatsappMessageIntentEnum;
use App\Core\Infra\External\PythonWhatsappMessageBridge;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use RuntimeException;
use Throwable;

#[Signature('whatsapp:bridge')]
#[Description('Executa o bot Python do WhatsApp e envia as mensagens recebidas para a aplicação.')]
class WhatsAppBridgeCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(
        PythonWhatsappMessageBridge $bridge,
        ProcessWhatsappMessageUsecaseInterface $processWhatsappMessage,
        WhatsappBotLoggerInterface $logger,
    ): int {
        $this->info('Iniciando ponte Python/PHP do WhatsApp...');
        $logger->botStarted(['source' => 'python-whatsapp']);

        try {
            $exitCode = $bridge->stream(
                handleMessage: function (ReceivedMessageInputDTO $message, callable $sendReply) use ($processWhatsappMessage, $logger): void {
                    $startedAt = microtime(true);
                    $sender = $message->senderName ?? $message->phone ?? 'Contato não identificado';
                    $displayMessage = trim($message->message) !== '' ? $message->message : '[mensagem sem texto]';
                    $logger->messageDetected($this->messageLogContext($message));
                    $logger->messageProcessingStarted($this->messageLogContext($message));

                    $this->line(sprintf(
                        '[%s] %s: %s',
                        $message->receivedAt ?? now()->format('H:i'),
                        $sender,
                        $displayMessage,
                    ));
                    $this->line(sprintf(
                        'Payload recebido da ponte: source=%s external_id=%s',
                        $message->source ?? 'python-whatsapp',
                        $message->externalId ?? 'sem-id',
                    ));

                    $result = $processWhatsappMessage($message);
                    $reply = (string) ($result['reply'] ?? '');
                    $resultLogContext = $this->resultLogContext($message, $result, $startedAt, $reply);

                    $logger->messageInterpreted($resultLogContext);

                    if (($result['intent'] ?? null) === WhatsappMessageIntentEnum::SEARCH_TECHNICAL_NOTEBOOK->value) {
                        $logger->searchFinished($resultLogContext);
                    }

                    $this->line(sprintf(
                        'Resposta recebida da API: intent=%s total=%d caracteres=%d',
                        (string) ($result['intent'] ?? 'desconhecido'),
                        (int) ($result['total'] ?? 0),
                        mb_strlen($reply),
                    ));

                    if ($reply === '') {
                        $this->line(sprintf(
                            'Nenhuma resposta enviada para %s. Tempo total: %.2fs.',
                            $sender,
                            microtime(true) - $startedAt,
                        ));
                        $logger->replySkipped(array_merge($resultLogContext, [
                            'reason' => 'empty_reply',
                            'total_duration_ms' => $this->elapsedMilliseconds($startedAt),
                        ]));

                        return;
                    }

                    $sendReply($reply);
                    $logger->replySent(array_merge($resultLogContext, [
                        'total_duration_ms' => $this->elapsedMilliseconds($startedAt),
                    ]));
                    $this->line(sprintf(
                        'Resposta enviada para %s. Tempo total: %.2fs.',
                        $sender,
                        microtime(true) - $startedAt,
                    ));
                },
                handleError: function (string $error) use ($logger): void {
                    $this->error(trim($error));
                    $logger->botError(new RuntimeException(trim($error) ?: 'Erro no processo Python do WhatsApp'), [
                        'source' => 'python-whatsapp',
                    ]);
                },
                handleStatus: function (string $status) use ($logger): void {
                    $this->line($status);
                    $this->logBridgeStatus($logger, $status);
                },
            );
        } catch (Throwable $throwable) {
            $logger->botCritical($throwable, ['source' => 'python-whatsapp']);
            $this->error($throwable->getMessage());

            return self::FAILURE;
        }

        if ($exitCode !== self::SUCCESS) {
            $logger->botCritical(new RuntimeException(sprintf(
                'Processo Python do WhatsApp finalizado com código %d.',
                $exitCode,
            )), [
                'source' => 'python-whatsapp',
                'reason' => 'bridge_exit_code',
            ]);
        }

        return $exitCode;
    }

    /**
     * @return array{external_id: string|null, sender: string|null, source: string}
     */
    private function messageLogContext(ReceivedMessageInputDTO $message): array
    {
        return [
            'external_id' => $message->externalId,
            'sender' => $message->senderName ?? $message->phone,
            'source' => $message->source ?? 'python-whatsapp',
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    private function resultLogContext(
        ReceivedMessageInputDTO $message,
        array $result,
        float $startedAt,
        string $reply,
    ): array {
        return array_merge($this->messageLogContext($message), [
            'intent' => (string) ($result['intent'] ?? 'unknown'),
            'filters' => is_array($result['filters'] ?? null) ? $result['filters'] : [],
            'result_total' => (int) ($result['total'] ?? 0),
            'reply_length' => mb_strlen($reply),
            'duration_ms' => $this->elapsedMilliseconds($startedAt),
        ]);
    }

    private function elapsedMilliseconds(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }

    private function logBridgeStatus(WhatsappBotLoggerInterface $logger, string $status): void
    {
        $status = trim($status);

        if ($status === '') {
            return;
        }

        $context = [
            'source' => 'python-whatsapp',
            'reason' => $status,
        ];

        if (str_starts_with($status, 'Mensagem ignorada') || str_starts_with($status, 'Mensagens ignoradas')) {
            $logger->messageIgnored($context);

            return;
        }

        if (str_starts_with($status, 'Bot ocupado')) {
            $logger->idleCycles($context);

            return;
        }

        if (str_starts_with($status, 'Erro ao ler mensagem do WhatsApp') || str_starts_with($status, 'Erro ao responder no WhatsApp')) {
            $logger->botError(new RuntimeException($status), [
                'source' => 'python-whatsapp',
            ]);
        }
    }
}
