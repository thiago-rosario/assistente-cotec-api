<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Infra\Message;

use App\TechnicalInspectionReport\Application\DTO\TechnicalInspectionReportDraftDTO;
use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportEntity;
use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportGoogleSheetEntity;

final class TechnicalInspectionReportWhatsappMessageBuilder
{
    public function menu(): array
    {
        return $this->response(
            'technical_inspection_report_menu',
            "📋 *Relatórios de Vistoria Técnica*\n\n"
            ."1️⃣ Cadastrar relatório\n"
            ."2️⃣ Consultar relatórios por município\n"
            .'0️⃣ Voltar ao menu principal',
        );
    }

    public function start(): array
    {
        return $this->response(
            'technical_inspection_report_started',
            "📝 *Cadastro de relatório iniciado!*\n\n"
            ."Informe o município da vistoria.\n\n"
            .'↩️ Digite 0 a qualquer momento para cancelar.',
        );
    }

    public function municipality(): array
    {
        return $this->response('technical_inspection_report_awaiting_municipality', '📍 Informe o município da vistoria técnica.');
    }

    public function consultationMunicipality(): array
    {
        return $this->response(
            'technical_inspection_report_awaiting_search_municipality',
            "🔎 *Consultar relatórios*\n\nInforme o município para localizar os relatórios de vistoria técnica.",
        );
    }

    public function seiDecision(): array
    {
        return $this->response(
            'technical_inspection_report_awaiting_sei_decision',
            "📌 Informe o número do processo SEI.\n\n"
            .'Se o relatório não possuir processo SEI vinculado, digite 1️⃣.',
        );
    }

    public function seiProcess(): array
    {
        return $this->response(
            'technical_inspection_report_awaiting_sei_process',
            '📄 Informe o número do processo SEI no formato 000.0000.0000.0000000-00.',
        );
    }

    public function inspectionDate(): array
    {
        return $this->response('technical_inspection_report_awaiting_inspection_date', '📅 Informe a data da vistoria no formato dd/mm/aaaa.');
    }

    public function responsiblePerson(): array
    {
        return $this->response('technical_inspection_report_awaiting_responsible', '👤 Informe o nome do responsável pela vistoria.');
    }

    public function document(): array
    {
        return $this->response(
            'technical_inspection_report_awaiting_document',
            '📎 Envie agora o Relatório de Vistoria Técnica como documento PDF.',
        );
    }

    public function confirmation(TechnicalInspectionReportDraftDTO $draft): array
    {
        $seiProcess = $draft->hasSeiProcess === true ? $draft->seiProcess : 'Não possui processo SEI vinculado';

        return $this->response(
            'technical_inspection_report_awaiting_confirmation',
            "✅ *Confira os dados do relatório:*\n\n"
            ."📍 Município: {$draft->municipality}\n"
            ."📄 Processo SEI: {$seiProcess}\n"
            ."📅 Data da vistoria: {$draft->inspectionDate}\n"
            ."👤 Responsável: {$draft->responsiblePerson}\n"
            ."📎 Documento: {$draft->documentName}\n\n"
            .'Digite 1️⃣ para confirmar ou 0️⃣ para cancelar.',
        );
    }

    /**
     * @param  list<TechnicalInspectionReportGoogleSheetEntity>  $reports
     */
    public function consultationResults(string $municipality, array $reports): array
    {
        $total = count($reports);
        $data = array_map($this->reportData(...), $reports);

        if ($total === 0) {
            return $this->response(
                'technical_inspection_report_no_results',
                "🔎 *Consulta de relatórios*\n\n"
                ."📍 Município: {$municipality}\n\n"
                ."❌ Não encontrei relatórios de vistoria técnica para este município.\n\n"
                .'↩️ Digite 1️⃣ para cadastrar um relatório, 2️⃣ para consultar outro município ou 0️⃣ para voltar.',
                filters: ['municipality' => $municipality],
            );
        }

        $label = $total === 1 ? 'relatório encontrado' : 'relatórios encontrados';
        $lines = [
            '🔎 *Relatórios de Vistoria Técnica*',
            '',
            "📍 Município: {$municipality}",
            "✅ {$total} {$label}:",
            '',
        ];

        foreach ($reports as $index => $report) {
            $number = $index + 1;
            $seiProcess = $report->seiProcess ?? 'Não possui processo SEI vinculado';

            $lines[] = "📄 *Relatório {$number}*";
            $lines[] = "📝 Nome: {$report->documentName}";
            $lines[] = "📍 Município: {$report->municipality}";
            $lines[] = "📄 Processo SEI: {$seiProcess}";
            $lines[] = "📅 Data da vistoria: {$report->inspectionDate}";
            $lines[] = "👤 Responsável: {$report->responsiblePerson}";
            $lines[] = '🔗 Acessar relatório:';
            $lines[] = $report->documentLink;
            $lines[] = '';
        }

        $lines[] = '↩️ Digite 1️⃣ para cadastrar, 2️⃣ para consultar outro município ou 0️⃣ para voltar.';

        return $this->response(
            'technical_inspection_report_results',
            implode("\n", $lines),
            total: $total,
            data: $data,
            filters: ['municipality' => $municipality],
        );
    }

    public function invalidMunicipality(): array
    {
        return $this->response('technical_inspection_report_invalid_municipality', '⚠️ Informe um município válido.');
    }

    public function invalidSearchMunicipality(): array
    {
        return $this->response(
            'technical_inspection_report_invalid_search_municipality',
            '⚠️ Informe um município válido para realizar a consulta.',
        );
    }

    public function invalidMenuOption(): array
    {
        return $this->response(
            'technical_inspection_report_invalid_menu_option',
            "⚠️ Opção inválida.\n\n"
            .'Digite 1️⃣ para cadastrar, 2️⃣ para consultar ou 0️⃣ para voltar.',
        );
    }

    public function invalidSeiDecision(): array
    {
        return $this->response(
            'technical_inspection_report_invalid_sei_decision',
            '⚠️ Digite 1️⃣ se não houver processo SEI ou informe o processo no formato solicitado.',
        );
    }

    public function invalidSeiProcess(): array
    {
        return $this->response('technical_inspection_report_invalid_sei_process', '⚠️ O processo SEI é inválido. Use o formato 000.0000.0000.0000000-00.');
    }

    public function invalidInspectionDate(): array
    {
        return $this->response('technical_inspection_report_invalid_inspection_date', '⚠️ A data é inválida. Informe uma data no formato dd/mm/aaaa.');
    }

    public function invalidResponsiblePerson(): array
    {
        return $this->response('technical_inspection_report_invalid_responsible', '⚠️ Informe o nome do responsável pela vistoria.');
    }

    public function invalidDocument(string $reason = 'Envie um documento PDF válido.'): array
    {
        return $this->response('technical_inspection_report_invalid_document', "⚠️ {$reason}");
    }

    public function storageFailure(string $reportId): array
    {
        return $this->response(
            'technical_inspection_report_recoverable_failure',
            "⚠️ Não consegui concluir o armazenamento do relatório {$reportId}.\n\n"
            .'Os dados foram preservados. Digite 1️⃣ para tentar novamente ou 0️⃣ para cancelar.',
        );
    }

    public function stored(TechnicalInspectionReportEntity $report, string $documentLink): array
    {
        $municipality = $report->municipality()?->value() ?? 'Não informado';
        $seiProcess = $report->seiProcess()?->value() ?? 'Não possui processo SEI vinculado';
        $inspectionDate = $report->inspectionDate()?->formatted() ?? 'Não informado';
        $responsiblePerson = $report->responsiblePerson()?->value() ?? 'Não informado';
        $link = trim($documentLink) === '' ? 'Link indisponível' : $documentLink;

        return $this->response(
            'technical_inspection_report_stored',
            "✅ *Relatório armazenado com sucesso!*\n\n"
            ."📍 Município: {$municipality}\n"
            ."📄 Processo SEI: {$seiProcess}\n"
            ."📅 Data da vistoria: {$inspectionDate}\n"
            ."👤 Responsável: {$responsiblePerson}\n\n"
            ."🔗 Acessar relatório:\n{$link}\n\n"
            ."📋 Escolha uma opção abaixo:\n"
            ."1️⃣ Cadastrar outro relatório\n"
            ."2️⃣ Consultar relatórios\n"
            .'0️⃣ Voltar ao menu principal',
        );
    }

    public function cancelled(): array
    {
        return $this->response(
            'technical_inspection_report_cancelled',
            "✅ Cadastro do Relatório de Vistoria Técnica cancelado.\n\n{$this->menu()['reply']}",
        );
    }

    public function processing(): array
    {
        return $this->response('technical_inspection_report_processing', '⏳ Estou armazenando o relatório. Aguarde um momento.');
    }

    public function expired(): array
    {
        return $this->response(
            'technical_inspection_report_expired',
            '⚠️ O cadastro anterior expirou. Vamos começar novamente. Informe o município da vistoria técnica.',
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function reportData(TechnicalInspectionReportGoogleSheetEntity $report): array
    {
        return [
            'report_id' => $report->reportId,
            'municipality' => $report->municipality,
            'sei_process' => $report->seiProcess,
            'inspection_date' => $report->inspectionDate,
            'responsible_person' => $report->responsiblePerson,
            'document_name' => $report->documentName,
            'document_link' => $report->documentLink,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $data
     * @param  array<string, mixed>  $filters
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    private function response(
        string $intent,
        string $reply,
        int $total = 0,
        array $data = [],
        array $filters = [],
    ): array {
        return [
            'reply' => $reply,
            'intent' => $intent,
            'total' => $total,
            'data' => $data,
            'filters' => $filters,
        ];
    }
}
