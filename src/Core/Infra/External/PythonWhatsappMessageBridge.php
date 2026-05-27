<?php

declare(strict_types=1);

namespace App\Core\Infra\External;

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Interfaces\PythonMessagePayloadAdapterInterface;
use Symfony\Component\Process\Process;

class PythonWhatsappMessageBridge
{
    public function __construct(
        private readonly PythonMessagePayloadAdapterInterface $adapter,
    ) {}

    /**
     * @param  callable(ReceivedMessageInputDTO): void  $handleMessage
     * @param  callable(string): void|null  $handleError
     */
    public function stream(callable $handleMessage, ?callable $handleError = null): int
    {
        $process = new Process($this->command());
        $process->setTimeout(null);
        $stdoutBuffer = '';

        $exitCode = $process->run(function (string $type, string $buffer) use ($handleMessage, $handleError, &$stdoutBuffer): void {
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
                $handleMessage($message);
            }
        });

        if (trim($stdoutBuffer) !== '') {
            foreach ($this->adapter->fromPythonOutput($stdoutBuffer) as $message) {
                $handleMessage($message);
            }
        }

        return $exitCode;
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
