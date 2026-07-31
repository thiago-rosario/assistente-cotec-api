<?php

declare(strict_types=1);

namespace App\Http\Helper;

use Illuminate\Support\Facades\Log;

class LogInfo
{
    public string $directoryName;

    public int $code;

    public string $message;

    public string $fileName;

    public int $lineNumber;

    public string $functionName;

    public function __construct(string $directoryName, string $fileName, int $lineNumber, string $functionName, int $code, string $message)
    {
        $this->directoryName = $directoryName;
        $this->fileName = $fileName;
        $this->lineNumber = $lineNumber;
        $this->functionName = $functionName;
        $this->code = $code;
        $this->message = $message;
    }

    public function toArray(): array
    {
        return [
            'directoryName' => $this->directoryName,
            'fileName' => $this->fileName,
            'lineNumber' => $this->lineNumber,
            'functionName' => $this->functionName,
            'code' => $this->code,
            'message' => $this->message,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    /**
     * @return array{
     *     phone: string,
     *     message: string,
     *     external_id: string|null,
     *     message_length: int,
     *     payload: array{
     *         action: string,
     *         message: array{telefone: string, msg: string, id_msg: string|null}
     *     }
     * }
     */
    public static function whatsappMessageSendSimulated(string $phone, string $message, ?string $externalId = null): array
    {
        $context = [
            'phone' => $phone,
            'message' => $message,
            'external_id' => $externalId,
            'message_length' => mb_strlen($message),
            'payload' => [
                'action' => 'EnviarMsg',
                'message' => [
                    'telefone' => $phone,
                    'msg' => $message,
                    'id_msg' => $externalId,
                ],
            ],
        ];

        Log::info('whatsapp_message_send_simulated', $context);

        return $context;
    }
}
