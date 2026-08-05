<?php

declare(strict_types=1);

use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportEntity;
use App\TechnicalInspectionReport\Domain\Repository\TechnicalInspectionReportDriveRepositoryInterface;
use App\TechnicalInspectionReport\Domain\ValueObject\ExternalMessageIdValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\InspectionDateValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\MunicipalityValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\ResponsiblePersonValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\SeiProcessValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\TechnicalInspectionReportFileValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\TechnicalInspectionReportIdValueObject;
use App\TechnicalInspectionReport\Enum\TechnicalInspectionReportExceptionCodeEnum;
use App\TechnicalInspectionReport\Enum\TechnicalInspectionReportStatus;
use App\TechnicalInspectionReport\Exception\IncompleteTechnicalInspectionReportException;
use App\TechnicalInspectionReport\Exception\InvalidExternalMessageIdException;
use App\TechnicalInspectionReport\Exception\InvalidInspectionDateException;
use App\TechnicalInspectionReport\Exception\InvalidMunicipalityException;
use App\TechnicalInspectionReport\Exception\InvalidResponsiblePersonException;
use App\TechnicalInspectionReport\Exception\InvalidSeiProcessException;
use App\TechnicalInspectionReport\Exception\InvalidTechnicalInspectionReportFileException;
use App\TechnicalInspectionReport\Exception\InvalidTechnicalInspectionReportIdException;
use App\TechnicalInspectionReport\Exception\InvalidTechnicalInspectionReportStateTransitionException;
use App\TechnicalInspectionReport\Exception\InvalidTechnicalInspectionReportValueException;
use App\TechnicalInspectionReport\Exception\TechnicalInspectionReportDomainException;

function technicalInspectionReportDocument(): TechnicalInspectionReportFileValueObject
{
    return new TechnicalInspectionReportFileValueObject(
        originalFileName: ' relatorio-vistoria.pdf ',
        mimeType: 'APPLICATION/PDF',
        sizeBytes: 2048,
    );
}

function technicalInspectionReportWithAllData(): TechnicalInspectionReportEntity
{
    return TechnicalInspectionReportEntity::start(
        id: new TechnicalInspectionReportIdValueObject('report-001'),
        externalMessageId: new ExternalMessageIdValueObject('message-001'),
    )
        ->provideResponsiblePerson(new ResponsiblePersonValueObject(' João   Silva '))
        ->provideInspectionDate(InspectionDateValueObject::fromBrazilianFormat('22/07/2026'))
        ->provideMunicipality(new MunicipalityValueObject(' Salvador '))
        ->declareNoSeiProcess()
        ->attachDocument(technicalInspectionReportDocument());
}

it('starts a report as a draft with identity and an undecided SEI process', function () {
    $report = TechnicalInspectionReportEntity::start(
        id: new TechnicalInspectionReportIdValueObject('report-001'),
        externalMessageId: new ExternalMessageIdValueObject('message-001'),
    );

    expect($report->status())->toBe(TechnicalInspectionReportStatus::Draft)
        ->and($report->id()->value())->toBe('report-001')
        ->and($report->externalMessageId()->value())->toBe('message-001')
        ->and($report->hasSeiProcessDecision())->toBeFalse()
        ->and($report->hasSeiProcess())->toBeFalse()
        ->and($report->hasDeclaredNoSeiProcess())->toBeFalse()
        ->and($report->isComplete())->toBeFalse();
});

it('compares reports by identity instead of their metadata', function () {
    $first = technicalInspectionReportWithAllData();
    $sameIdentity = TechnicalInspectionReportEntity::start(
        id: new TechnicalInspectionReportIdValueObject('report-001'),
        externalMessageId: new ExternalMessageIdValueObject('message-999'),
    );
    $differentIdentity = TechnicalInspectionReportEntity::start(
        id: new TechnicalInspectionReportIdValueObject('report-002'),
        externalMessageId: new ExternalMessageIdValueObject('message-001'),
    );

    expect($first->equals($sameIdentity))->toBeTrue()
        ->and($first->equals($differentIdentity))->toBeFalse();
});

it('keeps the SEI decision explicit', function () {
    $withoutProcess = TechnicalInspectionReportEntity::start(
        id: new TechnicalInspectionReportIdValueObject('report-001'),
        externalMessageId: new ExternalMessageIdValueObject('message-001'),
    )->declareNoSeiProcess();

    $withProcess = TechnicalInspectionReportEntity::start(
        id: new TechnicalInspectionReportIdValueObject('report-002'),
        externalMessageId: new ExternalMessageIdValueObject('message-002'),
    )->provideSeiProcess(new SeiProcessValueObject('012.3456.2026.0001234-00'));

    expect($withoutProcess->hasSeiProcessDecision())->toBeTrue()
        ->and($withoutProcess->hasDeclaredNoSeiProcess())->toBeTrue()
        ->and($withoutProcess->seiProcess())->toBeNull()
        ->and($withProcess->hasSeiProcessDecision())->toBeTrue()
        ->and($withProcess->hasSeiProcess())->toBeTrue()
        ->and($withProcess->seiProcess()?->value())->toBe('012.3456.2026.0001234-00');
});

it('allows metadata to be provided in any order and becomes complete only with all invariants', function () {
    $report = TechnicalInspectionReportEntity::start(
        id: new TechnicalInspectionReportIdValueObject('report-001'),
        externalMessageId: new ExternalMessageIdValueObject('message-001'),
    )
        ->provideInspectionDate(InspectionDateValueObject::fromBrazilianFormat('22/07/2026'))
        ->attachDocument(technicalInspectionReportDocument())
        ->provideMunicipality(new MunicipalityValueObject('Salvador'))
        ->provideResponsiblePerson(new ResponsiblePersonValueObject('João Silva'));

    expect($report->isComplete())->toBeFalse();

    $report->provideSeiProcess(new SeiProcessValueObject('012.3456.2026.0001234-00'));

    expect($report->isComplete())->toBeTrue()
        ->and($report->municipality()?->value())->toBe('Salvador')
        ->and($report->inspectionDate()?->iso8601())->toBe('2026-07-22')
        ->and($report->responsiblePerson()?->value())->toBe('João Silva')
        ->and($report->document()?->isPdf())->toBeTrue();
});

it('does not become ready while required data is missing', function () {
    $report = TechnicalInspectionReportEntity::start(
        id: new TechnicalInspectionReportIdValueObject('report-001'),
        externalMessageId: new ExternalMessageIdValueObject('message-001'),
    );

    $report->markReadyForStorage();
})->throws(IncompleteTechnicalInspectionReportException::class);

it('moves a complete report through valid storage states', function () {
    $report = technicalInspectionReportWithAllData()
        ->markReadyForStorage()
        ->beginStorage();

    expect($report->status())->toBe(TechnicalInspectionReportStatus::StoragePending);

    $report->confirmStorage();

    expect($report->status())->toBe(TechnicalInspectionReportStatus::Stored)
        ->and($report->isStored())->toBeTrue();
});

it('records an abstract storage failure without knowing the external provider', function () {
    $report = technicalInspectionReportWithAllData()
        ->markReadyForStorage()
        ->beginStorage()
        ->registerStorageFailure('O armazenamento não foi concluído.');

    expect($report->status())->toBe(TechnicalInspectionReportStatus::StorageFailed)
        ->and($report->storageFailureReason())->toBe('O armazenamento não foi concluído.');
});

it('rejects invalid lifecycle transitions', function (callable $operation) {
    $operation();
})->with([
    'begin storage before ready' => fn (): TechnicalInspectionReportEntity => technicalInspectionReportWithAllData()->beginStorage(),
    'confirm storage before pending' => fn (): TechnicalInspectionReportEntity => technicalInspectionReportWithAllData()->confirmStorage(),
    'failure before pending' => fn (): TechnicalInspectionReportEntity => technicalInspectionReportWithAllData()->registerStorageFailure(),
    'confirm storage twice' => function (): TechnicalInspectionReportEntity {
        $report = technicalInspectionReportWithAllData()->markReadyForStorage()->beginStorage();
        $report->confirmStorage();

        return $report->confirmStorage();
    },
])->throws(InvalidTechnicalInspectionReportStateTransitionException::class);

it('rejects metadata changes after the report leaves draft', function () {
    $report = technicalInspectionReportWithAllData()->markReadyForStorage();

    $report->provideMunicipality(new MunicipalityValueObject('Feira de Santana'));
})->throws(InvalidTechnicalInspectionReportStateTransitionException::class);

it('rejects all changes after storage succeeds', function () {
    $report = technicalInspectionReportWithAllData()
        ->markReadyForStorage()
        ->beginStorage()
        ->confirmStorage();

    $report->attachDocument(technicalInspectionReportDocument());
})->throws(InvalidTechnicalInspectionReportStateTransitionException::class);

it('normalizes value objects and compares them by value', function () {
    $municipality = new MunicipalityValueObject(' São   Luís ');
    $date = InspectionDateValueObject::fromBrazilianFormat('22/07/2026');
    $person = new ResponsiblePersonValueObject(' João   Silva ');
    $file = technicalInspectionReportDocument();

    expect($municipality->value())->toBe('São Luís')
        ->and($municipality->normalized())->toBe('sao luis')
        ->and($municipality->equals(new MunicipalityValueObject('sao luis')))->toBeTrue()
        ->and($date->formatted())->toBe('22/07/2026')
        ->and($date->iso8601())->toBe('2026-07-22')
        ->and($date->equals(InspectionDateValueObject::fromBrazilianFormat('22/07/2026')))->toBeTrue()
        ->and($person->value())->toBe('João Silva')
        ->and($file->originalFileName())->toBe('relatorio-vistoria.pdf')
        ->and($file->mimeType())->toBe(TechnicalInspectionReportFileValueObject::PdfMimeType)
        ->and($file->sizeBytes())->toBe(2048);
});

it('rejects invalid value objects', function (callable $factory, string $exceptionClass) {
    expect(fn () => $factory())->toThrow($exceptionClass);
})->with([
    'report id' => [
        fn (): TechnicalInspectionReportIdValueObject => new TechnicalInspectionReportIdValueObject(' '),
        InvalidTechnicalInspectionReportIdException::class,
    ],
    'external message id' => [
        fn (): ExternalMessageIdValueObject => new ExternalMessageIdValueObject(' '),
        InvalidExternalMessageIdException::class,
    ],
    'municipality' => [
        fn (): MunicipalityValueObject => new MunicipalityValueObject(' '),
        InvalidMunicipalityException::class,
    ],
    'responsible person' => [
        fn (): ResponsiblePersonValueObject => new ResponsiblePersonValueObject(' '),
        InvalidResponsiblePersonException::class,
    ],
    'SEI process' => [
        fn (): SeiProcessValueObject => new SeiProcessValueObject('invalid'),
        InvalidSeiProcessException::class,
    ],
    'inspection date' => [
        fn (): InspectionDateValueObject => InspectionDateValueObject::fromBrazilianFormat('31/02/2026'),
        InvalidInspectionDateException::class,
    ],
    'document name' => [
        fn (): TechnicalInspectionReportFileValueObject => new TechnicalInspectionReportFileValueObject('', 'application/pdf', 1),
        InvalidTechnicalInspectionReportFileException::class,
    ],
    'document MIME type' => [
        fn (): TechnicalInspectionReportFileValueObject => new TechnicalInspectionReportFileValueObject('report.pdf', 'text/plain', 1),
        InvalidTechnicalInspectionReportFileException::class,
    ],
    'document size' => [
        fn (): TechnicalInspectionReportFileValueObject => new TechnicalInspectionReportFileValueObject('report.pdf', 'application/pdf', 0),
        InvalidTechnicalInspectionReportFileException::class,
    ],
]);

it('assigns a dedicated code to every technical inspection report exception', function (callable $factory, int $expectedCode) {
    $exception = $factory();

    expect($exception)->toBeInstanceOf(RuntimeException::class)
        ->and($exception->getCode())->toBe($expectedCode);
})->with([
    'domain' => [fn (): TechnicalInspectionReportDomainException => new TechnicalInspectionReportDomainException, TechnicalInspectionReportExceptionCodeEnum::Domain->value],
    'invalid value' => [fn (): InvalidTechnicalInspectionReportValueException => new InvalidTechnicalInspectionReportValueException, TechnicalInspectionReportExceptionCodeEnum::InvalidValue->value],
    'report id' => [fn (): InvalidTechnicalInspectionReportIdException => new InvalidTechnicalInspectionReportIdException, TechnicalInspectionReportExceptionCodeEnum::InvalidReportId->value],
    'external message id' => [fn (): InvalidExternalMessageIdException => new InvalidExternalMessageIdException, TechnicalInspectionReportExceptionCodeEnum::InvalidExternalMessageId->value],
    'municipality' => [fn (): InvalidMunicipalityException => new InvalidMunicipalityException, TechnicalInspectionReportExceptionCodeEnum::InvalidMunicipality->value],
    'SEI process' => [fn (): InvalidSeiProcessException => new InvalidSeiProcessException, TechnicalInspectionReportExceptionCodeEnum::InvalidSeiProcess->value],
    'inspection date' => [fn (): InvalidInspectionDateException => new InvalidInspectionDateException, TechnicalInspectionReportExceptionCodeEnum::InvalidInspectionDate->value],
    'responsible person' => [fn (): InvalidResponsiblePersonException => new InvalidResponsiblePersonException, TechnicalInspectionReportExceptionCodeEnum::InvalidResponsiblePerson->value],
    'file' => [fn (): InvalidTechnicalInspectionReportFileException => new InvalidTechnicalInspectionReportFileException, TechnicalInspectionReportExceptionCodeEnum::InvalidFile->value],
    'incomplete report' => [fn (): IncompleteTechnicalInspectionReportException => new IncompleteTechnicalInspectionReportException, TechnicalInspectionReportExceptionCodeEnum::IncompleteReport->value],
    'state transition' => [fn (): InvalidTechnicalInspectionReportStateTransitionException => new InvalidTechnicalInspectionReportStateTransitionException, TechnicalInspectionReportExceptionCodeEnum::InvalidStateTransition->value],
]);

it('preserves custom exception message and previous exception', function () {
    $previous = new RuntimeException('causa original');
    $exception = new InvalidMunicipalityException('mensagem customizada', 2999, $previous);

    expect($exception->getMessage())->toBe('mensagem customizada')
        ->and($exception->getCode())->toBe(2999)
        ->and($exception->getPrevious())->toBe($previous);
});

it('uses immutable domain values and does not expose mutable aggregate properties', function () {
    $valueObjects = [
        MunicipalityValueObject::class,
        SeiProcessValueObject::class,
        InspectionDateValueObject::class,
        ResponsiblePersonValueObject::class,
        TechnicalInspectionReportFileValueObject::class,
        TechnicalInspectionReportIdValueObject::class,
        ExternalMessageIdValueObject::class,
    ];

    foreach ($valueObjects as $valueObject) {
        expect((new ReflectionClass($valueObject))->isReadOnly())->toBeTrue();
    }

    expect((new ReflectionClass(TechnicalInspectionReportEntity::class))->getProperties(ReflectionProperty::IS_PUBLIC))
        ->toBeEmpty();
});

it('exposes technology-free persistence operations', function () {
    $methods = get_class_methods(TechnicalInspectionReportDriveRepositoryInterface::class);

    expect($methods)
        ->toContain('save')
        ->toContain('findById')
        ->toContain('existsByExternalMessageId');
});
