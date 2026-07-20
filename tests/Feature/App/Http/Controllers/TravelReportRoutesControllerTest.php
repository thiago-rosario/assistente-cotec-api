<?php

use App\Core\TravelReport\Application\DTO\DeleteTravelReportInputDTO;
use App\Core\TravelReport\Application\DTO\DeleteTravelReportOutputDTO;
use App\Core\TravelReport\Application\DTO\FindTravelReportBySeiProcessInputDTO;
use App\Core\TravelReport\Application\DTO\FindTravelReportBySeiProcessOutputDTO;
use App\Core\TravelReport\Application\DTO\ListTravelReportByMunicipalityIdInputDTO;
use App\Core\TravelReport\Application\DTO\ListTravelReportsInputDTO;
use App\Core\TravelReport\Application\DTO\ListTravelReportsOutputDTO;
use App\Core\TravelReport\Application\DTO\PersistTravelReportOutputDTO;
use App\Core\TravelReport\Application\Interface\Usecase\DeleteTravelReportUsecaseInterface;
use App\Core\TravelReport\Application\Interface\Usecase\FindTravelReportBySeiProcessUsecaseInterface;
use App\Core\TravelReport\Application\Interface\Usecase\ListTravelReportByMunicipalityIdUsecaseInterface;
use App\Core\TravelReport\Application\Interface\Usecase\ListTravelReportsUsecaseInterface;
use Mockery\MockInterface;

it('lists travel reports and returns a jsend response', function (): void {
    $report = travelReportOutputDto();
    $output = new ListTravelReportsOutputDTO(total: 1, data: [$report]);

    $this->mock(ListTravelReportsUsecaseInterface::class, function (MockInterface $mock) use ($output): void {
        $mock->shouldReceive('__invoke')
            ->once()
            ->with(Mockery::type(ListTravelReportsInputDTO::class))
            ->andReturn($output);
    });

    $this->getJson('/api/travel-reports')
        ->assertSuccessful()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'total' => 1,
                'data' => [travelReportArray()],
            ],
        ]);
});

it('lists travel reports by municipality and returns a jsend response', function (): void {
    $report = travelReportOutputDto(municipalityId: 33);
    $output = new ListTravelReportsOutputDTO(total: 1, data: [$report]);

    $this->mock(ListTravelReportByMunicipalityIdUsecaseInterface::class, function (MockInterface $mock) use ($output): void {
        $mock->shouldReceive('__invoke')
            ->once()
            ->with(Mockery::on(
                fn (mixed $dto): bool => $dto instanceof ListTravelReportByMunicipalityIdInputDTO
                    && $dto->municipalityId === 33
            ))
            ->andReturn($output);
    });

    $this->getJson('/api/travel-reports/municipalities/33')
        ->assertSuccessful()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'total' => 1,
                'data' => [travelReportArray(municipalityId: 33)],
            ],
        ]);
});

it('finds a travel report by sei process and returns a jsend response', function (): void {
    $report = travelReportOutputDto(seiProcess: 'SEI-123');
    $output = new FindTravelReportBySeiProcessOutputDTO(data: $report);

    $this->mock(FindTravelReportBySeiProcessUsecaseInterface::class, function (MockInterface $mock) use ($output): void {
        $mock->shouldReceive('__invoke')
            ->once()
            ->with(Mockery::on(
                fn (mixed $dto): bool => $dto instanceof FindTravelReportBySeiProcessInputDTO
                    && $dto->seiProcess === 'SEI-123'
            ))
            ->andReturn($output);
    });

    $this->getJson('/api/travel-reports/sei-process/SEI-123')
        ->assertSuccessful()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'data' => travelReportArray(seiProcess: 'SEI-123'),
            ],
        ]);
});

it('deletes a travel report and returns a jsend response', function (): void {
    $output = new DeleteTravelReportOutputDTO(id: 10, deleted: true);

    $this->mock(DeleteTravelReportUsecaseInterface::class, function (MockInterface $mock) use ($output): void {
        $mock->shouldReceive('__invoke')
            ->once()
            ->with(Mockery::on(
                fn (mixed $dto): bool => $dto instanceof DeleteTravelReportInputDTO
                    && $dto->id === 10
            ))
            ->andReturn($output);
    });

    $this->deleteJson('/api/travel-reports/10')
        ->assertSuccessful()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'id' => 10,
                'deleted' => true,
            ],
        ]);
});

it('returns jsend validation errors for invalid travel report route parameters', function (): void {
    $this->mock(ListTravelReportByMunicipalityIdUsecaseInterface::class, function (MockInterface $mock): void {
        $mock->shouldReceive('__invoke')->never();
    });

    $this->getJson('/api/travel-reports/municipalities/invalid')
        ->assertUnprocessable()
        ->assertJson([
            'status' => 'fail',
            'data' => [
                'municipality_id' => ['The municipality id field must be an integer.'],
            ],
        ]);
});

function travelReportOutputDto(
    int $municipalityId = 1,
    string $seiProcess = 'SEI-001',
): PersistTravelReportOutputDTO {
    return new PersistTravelReportOutputDTO(
        id: 10,
        municipalityId: $municipalityId,
        submittedByUserId: 'user-123',
        fileName: 'relatorio.pdf',
        filePath: 'travel-reports/relatorio.pdf',
        fileSize: 2048,
        mimeType: 'application/pdf',
        seiProcess: $seiProcess,
    );
}

/**
 * @return array<string, mixed>
 */
function travelReportArray(
    int $municipalityId = 1,
    string $seiProcess = 'SEI-001',
): array {
    return [
        'id' => 10,
        'municipality_id' => $municipalityId,
        'submitted_by_user_id' => 'user-123',
        'file_name' => 'relatorio.pdf',
        'file_path' => 'travel-reports/relatorio.pdf',
        'sei_process' => $seiProcess,
        'file_size' => 2048,
        'mime_type' => 'application/pdf',
    ];
}
