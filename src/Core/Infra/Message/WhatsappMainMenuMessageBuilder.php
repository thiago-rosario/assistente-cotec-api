<?php

declare(strict_types=1);

namespace App\Core\Infra\Message;

use App\Core\Application\Interfaces\Message\WhatsappMainMenuMessageBuilderInterface;

class WhatsappMainMenuMessageBuilder implements WhatsappMainMenuMessageBuilderInterface
{
    private const string MainMenuMessage = "👋 Olá! Eu sou o Assistente da COTEC.\n\n"
        ."Como posso ajudar?\n\n"
        ."1️⃣  Consultar o Painel de Obras\n"
        ."2️⃣  Relatórios de Vistoria Técnica\n"
        ."3️⃣  Informações sobre o assistente\n"
        .'0️⃣  Encerrar atendimento';

    private const string TechnicalInspectionReportSoonMessage = '📋 A consulta ao Relatório de Vistoria Técnica estará disponível em breve.';

    private const string AssistantInfoMessage = 'ℹ️ Sou o Assistente da COTEC. Posso consultar o Painel de Obras, cadastrar relatórios de vistoria técnica e localizar relatórios por município.';

    private const string EndMessage = '👋 Atendimento encerrado. Quando precisar, envie uma nova mensagem para ver o menu.';

    private const string InvalidMenuOptionMessage = "⚠️ Não encontrei essa opção no menu.\n\n".self::MainMenuMessage;

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
    public function municipalityModuleChoice(string $municipality): array
    {
        return $this->emptyResponse(
            'municipality_module_choice',
            '🔎 Encontrei registros relacionados ao município *'.mb_strtoupper($municipality, 'UTF-8')."*.\n\n"
            ."Onde você deseja consultar?\n\n"
            ."1️⃣ *Painel de Obras*\n"
            ."2️⃣ *Relatórios de Vistoria Técnica*\n\n"
            .'Digite *1* ou *2* para continuar.',
            ['municipality' => $municipality],
        );
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
     * @param  array<string, mixed>  $filters
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    private function emptyResponse(string $intent, string $reply, array $filters = []): array
    {
        return [
            'reply' => $reply,
            'intent' => $intent,
            'total' => 0,
            'data' => [],
            'filters' => $filters,
        ];
    }
}
