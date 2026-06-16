<?php

declare(strict_types=1);

namespace App\Core\Infra\External;

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Interfaces\Adapter\PythonMessagePayloadAdapterInterface;
use JsonException;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

class PythonWhatsappMessageBridge
{
    public function __construct(
        private readonly PythonMessagePayloadAdapterInterface $adapter,
    ) {}

    /**
     * @param  callable(ReceivedMessageInputDTO, callable(string): void): void  $handleMessage
     * @param  callable(string): void|null  $handleError
     * @param  callable(string): void|null  $handleStatus
     */
    public function stream(callable $handleMessage, ?callable $handleError = null, ?callable $handleStatus = null): int
    {
        $process = new Process($this->command());
        $process->setTimeout(null);
        $input = new InputStream;
        $process->setInput($input);
        $stdoutBuffer = '';

        $exitCode = $process->run(function (string $type, string $buffer) use ($handleMessage, $handleError, $handleStatus, $input, &$stdoutBuffer): void {
            if ($type === Process::ERR) {
                $handleError ? $handleError($buffer) : null;

                return;
            }

            $stdoutBuffer .= $buffer;
            $lines = preg_split('/\R/', $stdoutBuffer);

            if ($lines === false) {
                return;
            }

            $stdoutBuffer = array_pop($lines) ?? '';
            $output = implode(PHP_EOL, $lines);

            $this->forwardStatusLines($output, $handleStatus);

            foreach ($this->adapter->fromPythonOutput($output) as $message) {
                $handleMessage(
                    $message,
                    function (string $reply) use ($input, $message): void {
                        $input->write($this->replyCommand($message, $reply));
                    },
                );
                $input->write($this->processedMessageCommand($message));
            }
        });

        if (trim($stdoutBuffer) !== '') {
            $this->forwardStatusLines($stdoutBuffer, $handleStatus);

            foreach ($this->adapter->fromPythonOutput($stdoutBuffer) as $message) {
                $handleMessage(
                    $message,
                    function (string $reply) use ($input, $message): void {
                        $input->write($this->replyCommand($message, $reply));
                    },
                );
                $input->write($this->processedMessageCommand($message));
            }
        }

        $input->close();

        return $exitCode;
    }

    private function forwardStatusLines(string $output, ?callable $handleStatus): void
    {
        if ($handleStatus === null) {
            return;
        }

        foreach (preg_split('/\R/', $output) ?: [] as $line) {
            $line = trim($line);

            if (! $this->isVisibleStatusLine($line)) {
                continue;
            }

            $handleStatus($line);
        }
    }

    private function isVisibleStatusLine(string $line): bool
    {
        return str_starts_with($line, 'Bridge iniciado')
            || str_starts_with($line, 'Mensagem nova detectada')
            || str_starts_with($line, 'Mensagem ignorada')
            || str_starts_with($line, 'Mensagens ignoradas')
            || str_starts_with($line, 'Payload enviado ao PHP/Laravel')
            || str_starts_with($line, 'Resposta enviada ao WhatsApp')
            || str_starts_with($line, 'Erro ao ler mensagem do WhatsApp')
            || str_starts_with($line, 'Erro ao responder no WhatsApp');
    }

    /**
     * @throws JsonException
     */
    public function replyCommand(ReceivedMessageInputDTO $message, string $reply): string
    {
        return json_encode([
            'type' => 'send_message',
            'payload' => [
                'customer_contact' => $this->customerContact($message),
                'content' => $reply,
                'external_id' => $message->externalId,
            ],
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE).PHP_EOL;
    }

    /**
     * @throws JsonException
     */
    public function processedMessageCommand(ReceivedMessageInputDTO $message): string
    {
        return json_encode([
            'type' => 'message_processed',
            'payload' => [
                'customer_contact' => $this->customerContact($message),
                'external_id' => $message->externalId,
            ],
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE).PHP_EOL;
    }

    /**
     * @return list<string>
     */
    public function command(string $pythonBinary = 'python3', ?string $scriptPath = null): array
    {
        return [
            $pythonBinary,
            $scriptPath ?? base_path('src/Core/Infra/External/Python/main.py'),
            '--bridge-output=json',
        ];
    }

    private function customerContact(ReceivedMessageInputDTO $message): ?string
    {
        return $message->phone ?? $message->senderName;
    }
}
