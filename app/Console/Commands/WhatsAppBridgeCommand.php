<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Interfaces\Usecase\ProcessWhatsappMessageUsecaseInterface;
use App\Core\Infra\External\PythonWhatsappMessageBridge;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('whatsapp:bridge')]
#[Description('Legado: executa a ponte Python/Selenium do WhatsApp quando WHATSAPP_TRANSPORT=python_bridge.')]
class WhatsAppBridgeCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (config('whatsapp.transport') !== 'python_bridge') {
            $this->warn('A ponte Python/Selenium do WhatsApp está depreciada e não será iniciada.');
            $this->warn('Defina WHATSAPP_TRANSPORT=python_bridge apenas para usar o fallback legado.');

            return self::SUCCESS;
        }

        $bridge = $this->laravel->make(PythonWhatsappMessageBridge::class);
        $processWhatsappMessage = $this->laravel->make(ProcessWhatsappMessageUsecaseInterface::class);

        $this->info('Iniciando ponte Python/PHP legada do WhatsApp...');

        return $bridge->stream(
            handleMessage: function (ReceivedMessageInputDTO $message, callable $sendReply) use ($processWhatsappMessage): void {
                $startedAt = microtime(true);
                $sender = $message->senderName ?? $message->phone ?? 'Contato não identificado';
                $displayMessage = trim($message->message) !== '' ? $message->message : '[mensagem sem texto]';

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

                    return;
                }

                $sendReply($reply);
                $this->line(sprintf(
                    'Resposta enviada para %s. Tempo total: %.2fs.',
                    $sender,
                    microtime(true) - $startedAt,
                ));
            },
            handleError: function (string $error): void {
                $this->error(trim($error));
            },
            handleStatus: function (string $status): void {
                $this->line($status);
            },
        );
    }
}
