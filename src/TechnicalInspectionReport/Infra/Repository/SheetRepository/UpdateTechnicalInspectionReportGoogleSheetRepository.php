<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Infra\Repository\SheetRepository;

use App\TechnicalInspectionReport\Application\DTO\RegisterTechnicalInspectionReportCatalogInputDTO;
use App\TechnicalInspectionReport\Infra\Trait\HandlesTechnicalInspectionReportGoogleSheetRows;
use Revolution\Google\Sheets\Facades\Sheets;
use RuntimeException;

class UpdateTechnicalInspectionReportGoogleSheetRepository
{
    use HandlesTechnicalInspectionReportGoogleSheetRows;

    public function __construct(
        private readonly FindAllTechnicalInspectionReportGoogleSheetRepository $findAllRepository,
    ) {}

    public function update(RegisterTechnicalInspectionReportCatalogInputDTO $input): void
    {
        $rowNumber = $input->sheet->rowNumber() ?? $this->findRowNumber($input->sheet->reportId);

        if ($rowNumber === null) {
            throw new RuntimeException(
                'Não foi encontrada uma linha para o relatório de vistoria técnica informado.',
            );
        }

        Sheets::spreadsheet($this->spreadsheetId())
            ->sheet($this->sheetName())
            ->range("A{$rowNumber}:I{$rowNumber}")
            ->update([$input->sheet->toOrderedSheetRow()]);
    }

    private function findRowNumber(string $reportId): ?int
    {
        foreach ($this->findAllRepository->findAllSheet() as $report) {
            if ($report->reportId === $reportId) {
                return $report->rowNumber();
            }
        }

        return null;
    }
}
