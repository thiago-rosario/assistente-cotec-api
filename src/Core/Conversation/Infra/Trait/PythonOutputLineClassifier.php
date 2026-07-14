<?php

declare(strict_types=1);

namespace App\Core\Conversation\Infra\Trait;

trait PythonOutputLineClassifier
{
    public function isStatusLine(string $line): bool
    {
        return str_starts_with($line, 'Aguardando login no WhatsApp Web')
            || str_starts_with($line, 'Nenhuma mensagem nova encontrada')
            || str_starts_with($line, 'Nenhuma nova mensagem recebida')
            || str_starts_with($line, 'Bridge iniciado')
            || str_starts_with($line, 'Mensagem nova detectada')
            || str_starts_with($line, 'Mensagem ignorada')
            || str_starts_with($line, 'Mensagens ignoradas')
            || str_starts_with($line, 'Payload enviado ao PHP/Laravel')
            || str_starts_with($line, 'Resposta enviada ao WhatsApp')
            || str_starts_with($line, 'Bot ocupado')
            || str_starts_with($line, 'Erro ao ler mensagem do WhatsApp')
            || str_starts_with($line, 'Erro ao responder no WhatsApp')
            || str_starts_with($line, 'File "')
            || str_starts_with($line, 'KeyboardInterrupt');
    }

    public function isTracebackLine(string $line): bool
    {
        return str_starts_with($line, '^C')
            || str_starts_with($line, 'Traceback ');
    }
}
