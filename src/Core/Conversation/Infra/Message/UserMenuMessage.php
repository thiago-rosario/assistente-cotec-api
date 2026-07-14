<?php

declare(strict_types=1);

namespace App\Core\Conversation\Infra\Message;

use App\Core\Conversation\Enum\MainMenuOption;

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
        return MainMenuOption::fromInput($option) !== null;
    }

    private function label(MainMenuOption $option): string
    {
        return match ($option) {
            MainMenuOption::BuildPanelConsultation => 'Consultar o Painel de Obras',
            MainMenuOption::TravelReportSubmission => 'Enviar Relatório de Viagem',
            MainMenuOption::TravelReportConsultation => 'Consultar Relatório de Viagem',
            MainMenuOption::AssistantInformation => 'Informações sobre o assistente',
            MainMenuOption::CloseAttendance => 'Encerrar atendimento',
        };
    }

    private function line(MainMenuOption $option): string
    {
        return sprintf('%s - %s', $option->value, $this->label($option));
    }

    private function optionsText(): string
    {
        return implode(
            "\n",
            array_map(
                fn (MainMenuOption $option): string => $this->line($option),
                MainMenuOption::cases(),
            ),
        );
    }
}
