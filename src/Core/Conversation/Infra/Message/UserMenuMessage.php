<?php

declare(strict_types=1);

namespace App\Core\Conversation\Infra\Message;

use App\Core\Conversation\Enum\MainMenuOptionEnum;

final readonly class UserMenuMessage
{
    private const string Header = "Olá! Eu sou o Assistente da CEIRF.\n\nComo posso ajudar?";

    private const string Prompt = 'Digite o número da opção desejada.';

    public function content(): string
    {
        return self::Header."\n\n"
            .$this->optionsText()."\n\n"
            .self::Prompt;
    }

    public function accepts(string $option): bool
    {
        return MainMenuOptionEnum::tryFrom(trim($option)) !== null;
    }

    private function label(MainMenuOptionEnum $option): string
    {
        return match ($option) {
            MainMenuOptionEnum::BuildPanelConsultation => 'Consultar o Painel de Obras',
            MainMenuOptionEnum::TravelReportSubmission => 'Enviar Relatório de Vistoria Tecnica',
            MainMenuOptionEnum::TravelReportConsultation => 'Consultar Relatório de Vistoria Tecnica',
            MainMenuOptionEnum::AssistantInformation => 'Informações sobre o assistente',
            MainMenuOptionEnum::CloseAttendance => 'Encerrar atendimento',
        };
    }

    private function line(MainMenuOptionEnum $option): string
    {
        return sprintf('%s - %s', $option->value, $this->label($option));
    }

    private function optionsText(): string
    {
        return implode(
            "\n",
            array_map(
                fn (MainMenuOptionEnum $option): string => $this->line($option),
                MainMenuOptionEnum::cases(),
            ),
        );
    }
}
