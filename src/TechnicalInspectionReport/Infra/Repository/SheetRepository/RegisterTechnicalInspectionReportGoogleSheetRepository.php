<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Infra\Repository\SheetRepository;

use App\Core\Infra\External\GoogleAuthenticationService;
use App\TechnicalInspectionReport\Application\DTO\RegisterTechnicalInspectionReportCatalogInputDTO;
use App\TechnicalInspectionReport\Infra\Trait\HandlesTechnicalInspectionReportGoogleSheetRows;
use Revolution\Google\Sheets\Facades\Sheets;

class RegisterTechnicalInspectionReportGoogleSheetRepository
{
    use HandlesTechnicalInspectionReportGoogleSheetRows;

    public function __construct(
        private readonly ?GoogleAuthenticationService $googleAuthentication = null,
    ) {}

    public function register(RegisterTechnicalInspectionReportCatalogInputDTO $input): void
    {
        ($this->googleAuthentication ?? app(GoogleAuthenticationService::class))->authenticate();

        Sheets::spreadsheet($this->spreadsheetId())
            ->sheet($this->sheetName())
            ->append([$input->sheet->toOrderedSheetRow()]);
    }
}
