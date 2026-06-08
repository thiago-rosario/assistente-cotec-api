<?php

declare(strict_types=1);

namespace App\Core\Infra\External;

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Interfaces\PythonMessagePayloadAdapterInterface;
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
     */
    public function stream(callable $handleMessage, ?callable $handleError = null): int
    {
        $process = new Process($this->command());
        $process->setTimeout(null);
        $input = new InputStream;
        $process->setInput($input);
        $stdoutBuffer = '';

        $exitCode = $process->run(function (string $type, string $buffer) use ($handleMessage, $handleError, $input, &$stdoutBuffer): void {
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

            foreach ($this->adapter->fromPythonOutput(implode(PHP_EOL, $lines)) as $message) {
                $handleMessage(
                    $message,
                    function (string $reply) use ($input, $message): void {
                        $input->write($this->replyCommand($message, $reply));
                    },
                );
            }
        });

        if (trim($stdoutBuffer) !== '') {
            foreach ($this->adapter->fromPythonOutput($stdoutBuffer) as $message) {
                $handleMessage(
                    $message,
                    function (string $reply) use ($input, $message): void {
                        $input->write($this->replyCommand($message, $reply));
                    },
                );
            }
        }

        $input->close();

        return $exitCode;
    }

    /**
     * @throws JsonException
     */
    public function replyCommand(ReceivedMessageInputDTO $message, string $reply): string
    {
        return json_encode([
            'type' => 'send_message',
            'payload' => [
                'customer_contact' => $message->phone ?? $message->senderName,
                'content' => $reply,
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
}
