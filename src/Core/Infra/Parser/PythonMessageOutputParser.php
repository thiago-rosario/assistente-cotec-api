<?php

declare(strict_types=1);

namespace App\Core\Infra\Parser;

use App\Core\Application\Interfaces\Parser\PythonBridgeEventParserInterface;
use App\Core\Application\Interfaces\Parser\PythonMessageOutputParserInterface;
use App\Core\Infra\Trait\PythonOutputLineClassifier;
use Illuminate\Support\Str;

class PythonMessageOutputParser implements PythonMessageOutputParserInterface
{
    use PythonOutputLineClassifier;

    public function __construct(
        private readonly PythonBridgeEventParserInterface $bridgeEventParser,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function parse(string $output): array
    {
        $messages = [];
        $currentMessage = null;

        foreach (preg_split('/\R/', $output) ?: [] as $line) {
            $line = trim($line);

            if ($line === '' || $this->isStatusLine($line) || $this->isTracebackLine($line)) {
                continue;
            }

            $bridgePayload = $this->bridgeEventParser->parse($line);

            if ($bridgePayload !== null) {
                $this->pushCompletedMessage($messages, $currentMessage);
                $messages[] = $bridgePayload;

                continue;
            }

            if (Str::startsWith($line, 'Mensagem recebida de:')) {
                $this->pushCompletedMessage($messages, $currentMessage);

                $currentMessage = [
                    'customer_contact' => trim(Str::after($line, 'Mensagem recebida de:')),
                    'source' => 'python-whatsapp',
                    'metadata' => [
                        'raw_lines' => [$line],
                    ],
                ];

                continue;
            }

            if (Str::startsWith($line, 'Conteúdo da mensagem:') && $currentMessage !== null) {
                $currentMessage['message'] = trim(Str::after($line, 'Conteúdo da mensagem:'));
                $currentMessage['metadata']['raw_lines'][] = $line;

                continue;
            }

            if (preg_match('/^\d{1,2}:\d{2}$/', $line) === 1 && $currentMessage !== null) {
                $currentMessage['received_at'] = $line;
                $currentMessage['metadata']['raw_lines'][] = $line;
            }
        }

        $this->pushCompletedMessage($messages, $currentMessage);

        return $messages;
    }

    /**
     * @param  list<array<string, mixed>>  $messages
     * @param  array<string, mixed>|null  $message
     */
    private function pushCompletedMessage(array &$messages, ?array &$message): void
    {
        if (isset($message['message'])) {
            $messages[] = $message;
        }

        $message = null;
    }
}
