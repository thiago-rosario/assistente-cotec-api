<?php

use App\Core\TravelReport\Application\DTO\PersistTravelReportOutputDTO;

it('stores persisted travel report attachment output data', function (): void {
    $dto = new PersistTravelReportOutputDTO(
        id: 15,
        municipalityId: 1,
        submittedByUserId: 'user-1',
        fileName: 'relatorio.pdf',
        filePath: 'travel-reports/relatorio.pdf',
        fileSize: 2048,
        mimeType: 'application/pdf',
        seiProcess: 'SEI-12345',
    );

    expect($dto->id)->toBe(15)
        ->and($dto->municipalityId)->toBe(1)
        ->and($dto->submittedByUserId)->toBe('user-1')
        ->and($dto->fileName)->toBe('relatorio.pdf')
        ->and($dto->filePath)->toBe('travel-reports/relatorio.pdf')
        ->and($dto->fileSize)->toBe(2048)
        ->and($dto->mimeType)->toBe('application/pdf')
        ->and($dto->seiProcess)->toBe('SEI-12345');
});
