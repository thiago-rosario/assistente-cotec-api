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
#[Description('Executa o bot Python do WhatsApp e envia as mensagens recebidas para a aplicação.')]
class WhatsAppBridgeCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(
        PythonWhatsappMessageBridge $bridge,
        ProcessWhatsappMessageUsecaseInterface $processWhatsappMessage,
    ): int {
        $this->info('Iniciando ponte Python/PHP do WhatsApp...');

        return $bridge->stream(
            handleMessage: function (ReceivedMessageInputDTO $message, callable $sendReply) use ($processWhatsappMessage): void {
                $sender = $message->senderName ?? $message->phone ?? 'Contato não identificado';
                $displayMessage = trim($message->message) !== '' ? $message->message : '[mensagem sem texto]';

                $this->line(sprintf(
                    '[%s] %s: %s',
                    $message->receivedAt ?? now()->format('H:i'),
                    $sender,
                    $displayMessage,
                ));

                $result = $processWhatsappMessage($message);
                $reply = (string) ($result['reply'] ?? '');

                if ($reply === '') {
                    return;
                }

                $sendReply($reply);
                $this->line(sprintf('Resposta enviada para %s.', $sender));
            },
            handleError: function (string $error): void {
                $this->error(trim($error));
            },
        );
    }
}
