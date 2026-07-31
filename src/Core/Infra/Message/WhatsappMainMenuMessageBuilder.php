<?php

declare(strict_types=1);

namespace App\Core\Infra\Message;

use App\Core\Application\Interfaces\Message\WhatsappMainMenuMessageBuilderInterface;

class WhatsappMainMenuMessageBuilder implements WhatsappMainMenuMessageBuilderInterface
{
    private const string MainMenuMessage = "Olá! Eu sou o Assistente da CEIRF.\n\n"
        ."Como posso ajudar?\n\n"
        ."1 - Consultar o Painel de Obras\n"
        ."2 - Consultar Relatório de Vistoria Técnica\n"
        ."3 - Informações sobre o assistente\n"
        .'0 - Encerrar atendimento';

    private const string TechnicalInspectionReportSoonMessage = 'A consulta ao Relatório de Vistoria Técnica estará disponível em breve.';

    private const string AssistantInfoMessage = 'Sou o Assistente da CEIRF. Neste momento, posso encaminhar consultas ao Painel de Obras e manter o atendimento organizado pelo menu principal.';

    private const string EndMessage = 'Atendimento encerrado. Quando precisar, envie uma nova mensagem para ver o menu.';

    private const string InvalidMenuOptionMessage = "Não encontrei essa opção no menu.\n\n".self::MainMenuMessage;

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function mainMenu(): array
    {
        return $this->emptyResponse('main_menu', self::MainMenuMessage);
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function technicalInspectionReportUnavailable(): array
    {
        return $this->emptyResponse(
            'technical_inspection_report_unavailable',
            self::TechnicalInspectionReportSoonMessage,
        );
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function assistantInfo(): array
    {
        return $this->emptyResponse('assistant_info', self::AssistantInfoMessage);
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function conversationEnded(): array
    {
        return $this->emptyResponse('conversation_ended', self::EndMessage);
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function invalidMenuOption(): array
    {
        return $this->emptyResponse('invalid_menu_option', self::InvalidMenuOptionMessage);
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    private function emptyResponse(string $intent, string $reply): array
    {
        return [
            'reply' => $reply,
            'intent' => $intent,
            'total' => 0,
            'data' => [],
            'filters' => [],
        ];
    }
}
