<?php

declare(strict_types=1);

namespace App\Contract\Infra\Message;

class WhatsappContractDefaultReplies
{
    private const string MenuMessage = "Olá! Seja bem-vindo ao módulo de *Acompanhamento de Contratos*. 👋\n\n"
    . "Por aqui, você pode consultar informações sobre contratos, aditivos, reajustes, reequilíbrios e prazos de execução.\n\n"
    . "Primeiro, escolha o tipo de acompanhamento que deseja realizar. Em seguida, solicitaremos uma das informações necessárias para localizar o contrato, como:\n\n"
    . "• Nome do município\n"
    . "• Nome da empresa\n"
    . "• Número do contrato\n\n"
    . "As formas de pesquisa disponíveis podem variar conforme a opção escolhida.\n\n"
    . "*Selecione uma opção:*\n\n"
    . "1️⃣ Aditivos de valor\n"
    . "2️⃣ Reajustes e reequilíbrios\n"
    . "3️⃣ Controle de prazos de execução\n"
    . "4️⃣ Resumo completo do contrato\n\n"
    . "0️⃣ Voltar ao menu principal\n\n"
    . "Digite apenas o número da opção desejada.";

    private const string ValueAdditiveSearchMessage = "💰 *ADITIVOS DE VALOR*\n\n"
        ."Informe uma das opções abaixo:\n\n"
        ."• Nome do município\n"
        ."• Nome da empresa\n"
        ."• Número do contrato\n\n"
        ."Exemplos:\n"
        ."IBOTIRAMA\n"
        ."UFC ENGENHARIA\n"
        ."148/2024\n\n"
        ."Para um melhor atendimento, envie apenas uma informação por vez.\n\n"
        .'Digite 0 para voltar.';

    private const string ContractAdjustmentSearchMessage = "📊 *REAJUSTES E REEQUILÍBRIOS*\n\n"
        ."Informe uma das opções abaixo:\n\n"
        ."• Nome do município\n"
        ."• Nome da empresa\n"
        ."• Número do contrato\n\n"
        ."Exemplos:\n"
        ."SALVADOR\n"
        ."GRADO ENGENHARIA LTDA\n"
        ."05/2022\n\n"
        ."Para um melhor atendimento, envie apenas uma informação por vez.\n\n"
        .'Digite 0 para voltar.';

    private const string ExecutionDeadlineSearchMessage = "📅 *CONTROLE DE PRAZOS DE EXECUÇÃO*\n\n"
        ."Informe uma das opções abaixo:\n\n"
        ."• Nome do município\n"
        ."• Nome da empresa\n"
        ."• Número do contrato\n\n"
        ."Exemplos:\n"
        ."SALVADOR\n"
        ."CONSÓRCIO INTEGRA\n"
        ."15/2022\n\n"
        ."Para um melhor atendimento, envie apenas uma informação por vez.\n\n"
        .'Digite 0 para voltar.';

    private const string ContractSummarySearchMessage = "📋 *RESUMO DO ACOMPANHAMENTO CONTRATUAL*\n\n"
        ."Informe uma das opções abaixo:\n\n"
        ."• Nome do município\n"
        ."• Número do contrato\n\n"
        ."Exemplos:\n"
        ."SALVADOR\n"
        ."13/2024\n\n"
        ."Para um melhor atendimento, envie apenas uma informação por vez.\n\n"
        .'Digite 0 para voltar.';

    private const string NoRecordsMessage = 'Não encontrei registros para essa consulta. Verifique a informação enviada e tente novamente.';

    private const string UnknownIntentMessage = 'Não consegui identificar exatamente qual consulta você deseja fazer. Envie somente uma das informações solicitadas.';

    private const string UnsupportedMessageContentMessage = 'Recebi sua mensagem, mas não consegui ler o conteúdo em texto. Envie a consulta em texto com o município, a empresa ou o número do contrato.';

    private const string RateLimitedMessage = 'Recebi sua mensagem, mas o serviço de interpretação está temporariamente no limite. Tente novamente em alguns instantes.';

    private const string DataSourceUnavailableMessage = 'Entendi sua consulta, mas não consegui acessar as informações dos contratos agora. Tente novamente em alguns instantes.';

    private const string ErrorMessage = 'Não consegui processar sua solicitação agora. Verifique a informação enviada e tente novamente.';

    private const string InvalidMenuOptionMessage = "Opção inválida.\n\n"
        ."Escolha uma das opções do menu de acompanhamento de contratos:\n\n"
        ."1️⃣ Aditivos de valor\n"
        ."2️⃣ Reajustes e reequilíbrios\n"
        ."3️⃣ Controle de prazos de execução\n"
        ."4️⃣ Resumo completo do contrato\n"
        .'0️⃣ Voltar ao menu principal';

    private const string ContinueSearchMessage = "🔄 Deseja realizar outra consulta?\n\n"
        ."1️⃣ Sim\n"
        ."2️⃣ Não\n"
        ."0️⃣ Voltar ao menu de contratos\n\n"
        .'Digite apenas o número da opção desejada.';

    private const string FinishedMessage = "✅ Consulta encerrada.\n\n"
        .'Quando precisar, envie uma nova mensagem para acessar o Assistente da COTEC.';

    public function menu(): string
    {
        return self::MenuMessage;
    }

    public function valueAdditiveSearch(): string
    {
        return self::ValueAdditiveSearchMessage;
    }

    public function contractAdjustmentSearch(): string
    {
        return self::ContractAdjustmentSearchMessage;
    }

    public function executionDeadlineSearch(): string
    {
        return self::ExecutionDeadlineSearchMessage;
    }

    public function contractSummarySearch(): string
    {
        return self::ContractSummarySearchMessage;
    }

    public function noRecords(): string
    {
        return self::NoRecordsMessage;
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

    public function invalidMenuOption(): string
    {
        return self::InvalidMenuOptionMessage;
    }

    public function continueSearch(): string
    {
        return self::ContinueSearchMessage;
    }

    public function finished(): string
    {
        return self::FinishedMessage;
    }
}
