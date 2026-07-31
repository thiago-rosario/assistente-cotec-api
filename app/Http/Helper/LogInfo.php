<?php

declare(strict_types=1);

namespace App\Http\Helper;

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
}
