<?php

use App\Core\TravelReport\Domain\Entity\TravelReportEntity;
use App\Core\TravelReport\Enum\TravelReportCodeExceptionEnum;
use App\Core\TravelReport\Exception\FileNameRequiredException;
use App\Core\TravelReport\Exception\FilePathRequiredException;
use App\Core\TravelReport\Exception\InvalidMunicipalityIdException;
use App\Core\TravelReport\Exception\SeiProcessRequiredException;
use App\Core\TravelReport\Exception\SubmittedByUserIdRequiredException;
use App\Models\TravelReport;
use Tests\TestCase;

uses(TestCase::class);

it('creates a valid travel report entity', function () {
    $entity = new TravelReportEntity(...validTravelReportAttributes());

    expect($entity)->toBeInstanceOf(TravelReportEntity::class);
});

it('exposes persisted travel report data as read-only attributes', function () {
    $createdAt = new DateTimeImmutable('2026-07-20 08:30:00');
    $updatedAt = new DateTimeImmutable('2026-07-20 08:30:00');

    $entity = new TravelReportEntity(
        id: 10,
        municipalityId: 1,
        submittedByUserId: 'user-1',
        fileName: 'relatorio.pdf',
        filePath: '/tmp/relatorio.pdf',
        fileSize: 2048,
        mimeType: 'application/pdf',
        seiProcess: 'SEI-12345',
        createdAt: $createdAt,
        updatedAt: $updatedAt,
    );

    expect($entity->id)->toBe(10)
        ->and($entity->municipalityId)->toBe(1)
        ->and($entity->submittedByUserId)->toBe('user-1')
        ->and($entity->fileName)->toBe('relatorio.pdf')
        ->and($entity->filePath)->toBe('/tmp/relatorio.pdf')
        ->and($entity->fileSize)->toBe(2048)
        ->and($entity->mimeType)->toBe('application/pdf')
        ->and($entity->seiProcess)->toBe('SEI-12345')
        ->and($entity->createdAt)->toBe($createdAt)
        ->and($entity->updatedAt)->toBe($updatedAt)
        ->and($entity->deletedAt)->toBeNull();
});

it('creates a new travel report submission with user and municipality references', function () {
    $submittedAt = new DateTimeImmutable('2026-07-20 08:30:00');

    $entity = TravelReportEntity::newSubmission(
        municipalityId: 1,
        submittedByUserId: 'user-1',
        fileName: 'relatorio.pdf',
        filePath: '/tmp/relatorio.pdf',
        seiProcess: 'SEI-12345',
        fileSize: 2048,
        submittedAt: $submittedAt,
    );

    expect($entity->id)->toBeNull()
        ->and($entity->municipalityId)->toBe(1)
        ->and($entity->submittedByUserId)->toBe('user-1')
        ->and($entity->seiProcess)->toBe('SEI-12345')
        ->and($entity->createdAt)->toBe($submittedAt)
        ->and($entity->updatedAt)->toBe($submittedAt);
});

it('returns persistence attributes for saving a submitted travel report', function () {
    $submittedAt = new DateTimeImmutable('2026-07-20 08:30:00');

    $entity = TravelReportEntity::newSubmission(
        municipalityId: 1,
        submittedByUserId: 'user-1',
        fileName: 'relatorio.pdf',
        filePath: '/tmp/relatorio.pdf',
        seiProcess: 'SEI-12345',
        fileSize: 2048,
        submittedAt: $submittedAt,
    );

    expect($entity->toPersistenceArray())->toBe([
        'municipality_id' => 1,
        'submitted_by_user_id' => 'user-1',
        'file_name' => 'relatorio.pdf',
        'file_path' => '/tmp/relatorio.pdf',
        'file_size' => 2048,
        'sei_process' => 'SEI-12345',
        'mime_type' => 'application/pdf',
        'created_at' => $submittedAt,
        'updated_at' => $submittedAt,
        'deleted_at' => null,
    ]);
});

it('creates a travel report entity from the eloquent model', function () {
    $model = new TravelReport;
    $model->setRawAttributes([
        'id' => 10,
        'municipality_id' => 1,
        'submitted_by_user_id' => 'user-1',
        'file_name' => 'relatorio.pdf',
        'file_path' => '/tmp/relatorio.pdf',
        'file_size' => 2048,
        'mime_type' => 'application/pdf',
        'sei_process' => 'SEI-12345',
        'created_at' => '2026-07-20 08:30:00',
        'updated_at' => '2026-07-20 08:30:00',
        'deleted_at' => null,
    ], true);

    $entity = TravelReportEntity::fromModel($model);

    expect($entity->id)->toBe(10)
        ->and($entity->municipalityId)->toBe(1)
        ->and($entity->submittedByUserId)->toBe('user-1')
        ->and($entity->fileName)->toBe('relatorio.pdf')
        ->and($entity->filePath)->toBe('/tmp/relatorio.pdf')
        ->and($entity->fileSize)->toBe(2048)
        ->and($entity->mimeType)->toBe('application/pdf')
        ->and($entity->seiProcess)->toBe('SEI-12345')
        ->and($entity->createdAt->format('Y-m-d H:i:s'))->toBe('2026-07-20 08:30:00')
        ->and($entity->updatedAt->format('Y-m-d H:i:s'))->toBe('2026-07-20 08:30:00')
        ->and($entity->deletedAt)->toBeNull();
});

it('does not expose domain operations that update travel reports after submission', function () {
    $entity = new TravelReportEntity(...validTravelReportAttributes());

    expect(method_exists($entity, 'update'))->toBeFalse()
        ->and(method_exists($entity, 'delete'))->toBeFalse();
});

it('prevents changing travel report attributes after construction', function () {
    $entity = new TravelReportEntity(...validTravelReportAttributes());

    $entity->fileName = 'relatorio-alterado.pdf';
})->throws(Error::class);

it('rejects access to unknown magic attributes', function () {
    $entity = new TravelReportEntity(...validTravelReportAttributes());

    $entity->unknownAttribute;
})->throws(LogicException::class);

it('validates travel report entity required data with domain exceptions', function (
    array $invalidAttributes,
    string $exception,
    string $message,
) {
    try {
        new TravelReportEntity(...array_merge(validTravelReportAttributes(), $invalidAttributes));
    } catch (Throwable $throwable) {
        expect($throwable)->toBeInstanceOf($exception)
            ->and($throwable->getMessage())->toBe($message);

        return;
    }

    $this->fail("Expected {$exception} to be thrown.");
})->with([
    'submitted by user id' => [
        ['submittedByUserId' => ' '],
        SubmittedByUserIdRequiredException::class,
        'O usuário responsável pelo envio é obrigatório.',
    ],
    'file name' => [
        ['fileName' => ' '],
        FileNameRequiredException::class,
        'O nome do arquivo do relatório é obrigatório.',
    ],
    'file path' => [
        ['filePath' => ' '],
        FilePathRequiredException::class,
        'O caminho do arquivo é obrigatório.',
    ],
    'municipality id' => [
        ['municipalityId' => 0],
        InvalidMunicipalityIdException::class,
        'O ID do município é inválido.',
    ],
    'sei process' => [
        ['seiProcess' => ' '],
        SeiProcessRequiredException::class,
        'O processo SEI do relatório é obrigatório.',
    ],
]);

it('defines travel report exception defaults', function (
    RuntimeException $exception,
    int $code,
    string $message,
) {
    expect($exception)->toBeInstanceOf(RuntimeException::class)
        ->and($exception->getCode())->toBe($code)
        ->and($exception->getMessage())->toBe($message);
})->with([
    'submitted by user id required' => [
        new SubmittedByUserIdRequiredException,
        TravelReportCodeExceptionEnum::SubmittedByUserIdRequired->value,
        'O usuário responsável pelo envio é obrigatório.',
    ],
    'file name required' => [
        new FileNameRequiredException,
        TravelReportCodeExceptionEnum::FileNameRequired->value,
        'O nome do arquivo do relatório é obrigatório.',
    ],
    'file path required' => [
        new FilePathRequiredException,
        TravelReportCodeExceptionEnum::FilePathRequired->value,
        'O caminho do arquivo é obrigatório.',
    ],
    'invalid municipality id' => [
        new InvalidMunicipalityIdException,
        TravelReportCodeExceptionEnum::InvalidMunicipalityId->value,
        'O ID do município é inválido.',
    ],
    'sei process required' => [
        new SeiProcessRequiredException,
        TravelReportCodeExceptionEnum::SeiProcessRequired->value,
        'O processo SEI do relatório é obrigatório.',
    ],
]);

/**
 * @return array<string, mixed>
 */
function validTravelReportAttributes(): array
{
    return [
        'municipalityId' => 1,
        'submittedByUserId' => 'user-1',
        'fileName' => 'relatorio.pdf',
        'filePath' => '/tmp/relatorio.pdf',
        'seiProcess' => 'SEI-12345',
    ];
}
