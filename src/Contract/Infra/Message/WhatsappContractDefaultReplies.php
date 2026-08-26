<?php

declare(strict_types=1);

namespace App\Contract\Infra\Message;

class WhatsappContractDefaultReplies
{
    private const string MenuMessage = "Olá! Seja bem-vindo ao módulo de *Acompanhamento de Contratos*. 👋\n\n"
    . "Aqui você pode consultar aditivos de valor, reajustes e reequilíbrios, prazos de execução e o resumo completo dos contratos.\n\n"
    . "*Selecione uma opção:*\n\n"
    . "1️⃣ Aditivos de valor\n"
    . "2️⃣ Reajustes e reequilíbrios\n"
    . "3️⃣ Controle de prazos de execução\n"
    . "4️⃣ Resumo completo do contrato\n\n"
    . "0️⃣ Voltar ao menu principal\n\n"
    . "Digite apenas o número da opção desejada.";

    private const string ValueAdditiveSearchMessage = "💰 *ADITIVOS DE VALOR*\n\n"
        ."Envie o município, a empresa ou o número do contrato (uma informação por vez).\n"
        ."Ex.: IBOTIRAMA, UFC ENGENHARIA ou 148/2024\n\n"
        .'Digite 0 para voltar.';

    private const string ContractAdjustmentSearchMessage = "📊 *REAJUSTES E REEQUILÍBRIOS*\n\n"
        ."Envie o município, a empresa ou o número do contrato (uma informação por vez).\n"
        ."Ex.: SALVADOR, GRADO ENGENHARIA LTDA ou 05/2022\n\n"
        .'Digite 0 para voltar.';

    private const string ExecutionDeadlineSearchMessage = "📅 *CONTROLE DE PRAZOS DE EXECUÇÃO*\n\n"
        ."Envie o município, a empresa ou o número do contrato (uma informação por vez).\n"
        ."Ex.: SALVADOR, CONSÓRCIO INTEGRA ou 15/2022\n\n"
        .'Digite 0 para voltar.';

    private const string ContractSummarySearchMessage = "📋 *RESUMO DO ACOMPANHAMENTO CONTRATUAL*\n\n"
        ."Envie o município ou o número do contrato.\n"
        ."Ex.: SALVADOR ou 13/2024\n\n"
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
