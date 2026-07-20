<?php

use App\Core\TravelReport\Application\DTO\PersistTravelReportInputDTO;
use App\Core\TravelReport\Application\DTO\PersistTravelReportOutputDTO;
use App\Core\TravelReport\Application\Interface\Usecase\PersistTravelReportUsecaseInterface;
use App\Core\TravelReport\Exception\InvalidMunicipalityIdException;
use Mockery\MockInterface;

it('persists a travel report and returns a jsend response', function (): void {
    $this->mock(PersistTravelReportUsecaseInterface::class, function (MockInterface $mock): void {
        $mock->shouldReceive('__invoke')
            ->once()
            ->with(Mockery::on(
                fn (mixed $dto): bool => $dto instanceof PersistTravelReportInputDTO
                    && $dto->municipalityId === 1
                    && $dto->submittedByUserId === 'user-123'
                    && $dto->fileName === 'relatorio.pdf'
                    && $dto->filePath === 'travel-reports/relatorio.pdf'
                    && $dto->fileSize === 2048
                    && $dto->mimeType === 'application/pdf'
                    && $dto->seiProcess === 'SEI-001'
            ))
            ->andReturn(new PersistTravelReportOutputDTO(
                id: 10,
                municipalityId: 1,
                submittedByUserId: 'user-123',
                fileName: 'relatorio.pdf',
                filePath: 'travel-reports/relatorio.pdf',
                fileSize: 2048,
                mimeType: 'application/pdf',
                seiProcess: 'SEI-001',
            ));
    });

    $this->postJson('/api/travel-reports', [
        'municipality_id' => 1,
        'submitted_by_user_id' => 'user-123',
        'file_name' => 'relatorio.pdf',
        'file_path' => 'travel-reports/relatorio.pdf',
        'file_size' => 2048,
        'mime_type' => 'application/pdf',
        'sei_process' => 'SEI-001',
    ])
        ->assertCreated()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'id' => 10,
                'municipality_id' => 1,
                'submitted_by_user_id' => 'user-123',
                'file_name' => 'relatorio.pdf',
                'file_path' => 'travel-reports/relatorio.pdf',
                'file_size' => 2048,
                'mime_type' => 'application/pdf',
                'sei_process' => 'SEI-001',
            ],
        ]);
});

it('returns jsend validation errors for invalid travel report data', function (): void {
    $this->mock(PersistTravelReportUsecaseInterface::class, function (MockInterface $mock): void {
        $mock->shouldReceive('__invoke')->never();
    });

    $this->postJson('/api/travel-reports', [
        'municipality_id' => 0,
    ])
        ->assertUnprocessable()
        ->assertJson([
            'status' => 'fail',
            'data' => [
                'municipality_id' => ['The municipality id field must be at least 1.'],
                'submitted_by_user_id' => ['The submitted by user id field is required.'],
                'file_name' => ['The file name field is required.'],
                'file_path' => ['The file path field is required.'],
                'sei_process' => ['The sei process field is required.'],
            ],
        ]);
});

it('returns a standardized error when travel report domain validation fails', function (): void {
    $this->mock(PersistTravelReportUsecaseInterface::class, function (MockInterface $mock): void {
        $mock->shouldReceive('__invoke')
            ->once()
            ->andThrow(new InvalidMunicipalityIdException);
    });

    $this->postJson('/api/travel-reports', [
        'municipality_id' => 1,
        'submitted_by_user_id' => 'user-123',
        'file_name' => 'relatorio.pdf',
        'file_path' => 'travel-reports/relatorio.pdf',
        'sei_process' => 'SEI-001',
    ])
        ->assertBadRequest()
        ->assertJson([
            'status' => 'error',
            'message' => 'O ID do município é inválido.',
            'code' => 1014,
            'data' => null,
        ]);
});
