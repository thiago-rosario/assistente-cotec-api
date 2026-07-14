<?php

declare(strict_types=1);

namespace App\Core\BuildPanel\Infra\Message;

use App\Core\Conversation\Application\Interfaces\Service\WhatsappDefaultRepliesInterface;
use App\Core\Conversation\Infra\Message\UserMenuMessage;

final readonly class WhatsappDefaultReplies implements WhatsappDefaultRepliesInterface
{
    private const string NoRecordsMessage = 'Não encontrei registros para essa consulta. Tente informar o nome do município ou o número do processo.';

    private const string UnknownIntentMessage = 'Não consegui identificar exatamente qual consulta você deseja fazer. Envie o número de uma opção do menu ou informe o nome do município ou o número do processo.';

    private const string UnsupportedMessageContentMessage = 'Recebi sua mensagem, mas não consegui ler conteúdo em texto. Envie uma opção do menu ou uma consulta em texto com o nome do município ou o número do processo.';

    private const string RateLimitedMessage = 'Recebi sua mensagem, mas o serviço de interpretação está temporariamente no limite. Tente novamente em alguns instantes.';

    private const string DataSourceUnavailableMessage = 'Entendi sua consulta, mas não consegui acessar a fonte de dados agora. Tente novamente em alguns instantes.';

    private const string ErrorMessage = 'Não consegui processar sua solicitação agora. Tente novamente informando uma opção do menu, o nome do município ou o número do processo.';

    public function __construct(
        private UserMenuMessage $userMenuMessage,
    ) {}

    public function noRecords(): string
    {
        return self::NoRecordsMessage;
    }

    public function greeting(): string
    {
        return $this->userMenuMessage->content();
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
