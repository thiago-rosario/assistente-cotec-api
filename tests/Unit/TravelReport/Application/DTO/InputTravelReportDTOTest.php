<?php

use App\Core\TravelReport\Application\DTO\PersistTravelReportInputDTO;

it('stores the travel report submission input data', function (): void {
    $dto = new PersistTravelReportInputDTO(
        municipalityId: 1,
        submittedByUserId: 'user-1',
        fileName: 'relatorio.pdf',
        filePath: '/tmp/relatorio.pdf',
        seiProcess: 'SEI-12345',
        fileSize: 2048,
    );

    expect($dto)->toBeInstanceOf(PersistTravelReportInputDTO::class)
        ->and($dto->municipalityId)->toBe(1)
        ->and($dto->submittedByUserId)->toBe('user-1')
        ->and($dto->fileName)->toBe('relatorio.pdf')
        ->and($dto->filePath)->toBe('/tmp/relatorio.pdf')
        ->and($dto->seiProcess)->toBe('SEI-12345')
        ->and($dto->fileSize)->toBe(2048)
        ->and($dto->mimeType)->toBe('application/pdf');
});

it('allows overriding the travel report mime type', function (): void {
    $dto = new PersistTravelReportInputDTO(
        municipalityId: 1,
        submittedByUserId: 'user-1',
        fileName: 'relatorio.pdf',
        filePath: '/tmp/relatorio.pdf',
        seiProcess: 'SEI-12345',
        mimeType: 'application/octet-stream',
    );

    expect($dto->fileSize)->toBeNull()
        ->and($dto->mimeType)->toBe('application/octet-stream');
});
