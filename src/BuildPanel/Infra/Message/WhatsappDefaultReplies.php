<?php

declare(strict_types=1);

namespace App\BuildPanel\Infra\Message;

class WhatsappDefaultReplies
{
    private const string NoRecordsMessage = 'Não encontrei registros para essa consulta. Tente informar o nome do município ou o número do processo.';

    private const string WelcomeMessage = "Olá! Bem-vindo ao módulo do *Painel de Obras da CEIRF/SSP*. 👋\n\n"
        ."Aqui eu posso trazer o extrato de uma obra por município ou por número de processo, com dados como situação do pleito, da licitação e do contrato, valor estimado e status da obra.\n\n"
        ."Para iniciar a consulta, envie uma das opções abaixo:\n\n"
        ."• Nome do município\n"
        ."• Número do processo\n\n"
        ."O processo pode ser referente à solicitação do pleito, à licitação ou ao contrato.\n\n"
        .'Envie apenas uma dessas informações por vez.';

    private const string UnknownIntentMessage = 'Não consegui identificar exatamente qual consulta você deseja fazer. Envie o nome do município ou o número do processo.';

    private const string UnsupportedMessageContentMessage = 'Recebi sua mensagem, mas não consegui ler conteúdo em texto. Envie a consulta em texto com o nome do município ou o número do processo.';

    private const string RateLimitedMessage = 'Recebi sua mensagem, mas o serviço de interpretação está temporariamente no limite. Tente novamente em alguns instantes.';

    private const string DataSourceUnavailableMessage = 'Entendi sua consulta, mas não consegui acessar a fonte de dados agora. Tente novamente em alguns instantes.';

    private const string ErrorMessage = 'Não consegui processar sua solicitação agora. Tente novamente informando o nome do município ou o número do processo.';

    private const string ConversationClosedMessage = "✅ Consulta encerrada.\n\n"
        ."🙏 Agradecemos por utilizar o Assistente da COTEC!\n"
        .'Quando precisar, envie uma nova mensagem para iniciar uma nova consulta.';

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

    public function conversationClosed(): string
    {
        return self::ConversationClosedMessage;
    }
}
