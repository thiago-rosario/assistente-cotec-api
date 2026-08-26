<?php

declare(strict_types=1);

namespace App\Core\Infra\Message;

class WhatsappCoreDefaultReplies
{
    private const string MainMenuMessage = "Olá! Eu sou o Assistente da COTEC. 👋\n\n"
."Posso ajudar você a consultar informações do *Painel de Obras da CEIRF/SSP* e acompanhar contratos.\n\n"
."Escolha uma das opções abaixo:\n\n"
."1️⃣ *Consultar o Painel de Obras*\n"
."Consulte informações por município ou número do processo.\n\n"
."2️⃣ *Acompanhar contratos*\n"
."Consulte aditivos, reajustes, prazos de execução e o resumo dos contratos.\n\n"
."Digite apenas o número da opção desejada.";

    private const string MunicipalityDisambiguationMessage = "Encontrei duas consultas disponíveis para o município *%s*.\n\n"
        ."Escolha o que deseja consultar:\n\n"
        ."1️⃣ Extrato de obras do município\n"
        ."2️⃣ Resumo das informações de contratos do município\n\n"
        ."0️⃣ Voltar ao menu principal\n\n"
        .'Digite apenas o número da opção desejada.';

    private const string InvalidMainMenuOptionMessage = "Opção inválida.\n\n"
        ."Escolha uma das opções do menu principal:\n\n"
        ."1️⃣ Consultar o Painel de Obras\n"
        ."2️⃣ Acompanhar contratos\n\n"
        ."0️⃣ Voltar ao menu principal\n\n"
        .'Digite apenas o número da opção desejada.';

    private const string UnsupportedMessageContentMessage = 'Recebi sua mensagem, mas não consegui ler o conteúdo em texto. Envie uma saudação, um município ou uma opção do menu.';

    public function mainMenu(): string
    {
        return self::MainMenuMessage;
    }

    public function municipalityDisambiguation(string $municipality): string
    {
        return sprintf(self::MunicipalityDisambiguationMessage, mb_strtoupper($municipality, 'UTF-8'));
    }

    public function invalidMainMenuOption(): string
    {
        return self::InvalidMainMenuOptionMessage;
    }

    public function unsupportedMessageContent(): string
    {
        return self::UnsupportedMessageContentMessage;
    }
}
