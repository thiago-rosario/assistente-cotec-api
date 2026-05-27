<?php

declare(strict_types=1);

namespace App\Core\Infra\Trait;

trait PythonOutputLineClassifier
{
    public function isStatusLine(string $line): bool
    {
        return str_starts_with($line, 'Aguardando login no WhatsApp Web')
            || str_starts_with($line, 'Nenhuma mensagem nova encontrada')
            || str_starts_with($line, 'File "')
            || str_starts_with($line, 'KeyboardInterrupt');
    }

    public function isTracebackLine(string $line): bool
    {
        return str_starts_with($line, '^C')
            || str_starts_with($line, 'Traceback ');
    }
}
