<?php

declare(strict_types=1);

namespace App\Core\Infra\Message;

class WhatsappDefaultReplies
{
    private const string NoRecordsMessage = 'Não encontrei registros para essa consulta. Tente informar o nome do município ou o número do processo.';

    private const string WelcomeMessage = "Olá! Eu sou o assistente da COTEC.\n\n"
    . "Posso te ajudar a consultar informações do *Painel de Obras da CEIRF/SSP*.\n\n"
    . "Para iniciar a consulta, envie uma das opções abaixo:\n\n"
    . "• Nome do município\n"
    . "• Número do processo\n\n"
    . "O processo pode ser referente à solicitação do pleito, à licitação ou ao contrato.\n\n"
    . "Para um melhor atendimento, envie apenas uma dessas informações por vez.";

    private const string UnknownIntentMessage = 'Não consegui identificar exatamente qual consulta você deseja fazer. Envie o nome do município ou o número do processo.';

    private const string UnsupportedMessageContentMessage = 'Recebi sua mensagem, mas não consegui ler conteúdo em texto. Envie a consulta em texto com o nome do município ou o número do processo.';

    private const string RateLimitedMessage = 'Recebi sua mensagem, mas o serviço de interpretação está temporariamente no limite. Tente novamente em alguns instantes.';

    private const string DataSourceUnavailableMessage = 'Entendi sua consulta, mas não consegui acessar a fonte de dados agora. Tente novamente em alguns instantes.';

    private const string ErrorMessage = 'Não consegui processar sua solicitação agora. Tente novamente informando o nome do município ou o número do processo.';

    public function noRecords(): string
    {
        return self::NoRecordsMessage;
    }

    public function greeting(): string
    {
        return self::WelcomeMessage;
    }

    public function unknownIntent(): string
    {
        return self::UnknownIntentMessage;
    }

    public function unsupportedMessageContent(): string
    {
        return self::UnsupportedMessageContentMessage;
    }

    public function rateLimited(): string
    {
        return self::RateLimitedMessage;
    }

    public function dataSourceUnavailable(): string
    {
        return self::DataSourceUnavailableMessage;
    }

    public function error(): string
    {
        return self::ErrorMessage;
    }
}
