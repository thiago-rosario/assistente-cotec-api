<?php

declare(strict_types=1);

use App\TravelReport\Domain\Entity\TravelReportDraftEntity;
use App\TravelReport\Domain\Enum\TravelReportConversationState;
use App\TravelReport\Domain\ValueObject\Municipality;
use App\TravelReport\Domain\ValueObject\ResponsibleName;
use App\TravelReport\Domain\ValueObject\SeiProcess;
use App\TravelReport\Domain\ValueObject\TravelDate;
use App\TravelReport\Domain\ValueObject\TravelReportDocument;

it('advances a travel report draft through the required conversation states', function () {
    $draft = TravelReportDraftEntity::start();

    expect($draft->state())->toBe(TravelReportConversationState::WaitingMunicipality);

    $draft = $draft->withMunicipality(new Municipality(' Salvador '));
    expect($draft->state())->toBe(TravelReportConversationState::WaitingProcess);

    $draft = $draft->withProcess(null);
    expect($draft->state())->toBe(TravelReportConversationState::WaitingDate);

    $draft = $draft->withTravelDate(TravelDate::fromBrazilianFormat('22/07/2026'));
    expect($draft->state())->toBe(TravelReportConversationState::WaitingResponsible);

    $draft = $draft->withResponsible(new ResponsibleName(' João   Silva '));

    expect($draft->state())->toBe(TravelReportConversationState::WaitingDocument)
        ->and($draft->isReadyForDocument())->toBeTrue()
        ->and($draft->municipality()?->value)->toBe('Salvador')
        ->and($draft->responsible()?->value)->toBe('João Silva');
});

it('normalizes searchable municipality and process values', function () {
    expect(new Municipality(' São   Luís ')->normalized())->toBe('sao luis')
        ->and(new Municipality('Salvador')->equals(new Municipality(' salvador ')))->toBeTrue()
        ->and(new SeiProcess(' 012.3456.2026.0001234-00 ')->normalized())
        ->toBe('012.3456.2026.0001234-00');
});

it('parses only valid Brazilian travel dates', function () {
    $date = TravelDate::fromBrazilianFormat('22/07/2026');

    expect($date->formatted())->toBe('22/07/2026')
        ->and($date->iso8601())->toBe('2026-07-22');
});

it('rejects invalid travel dates', function () {
    TravelDate::fromBrazilianFormat('31/02/2026');
})->throws(InvalidArgumentException::class, 'A data da viagem deve estar no formato dd/mm/aaaa.');

it('validates an already decoded PDF document within the configured size', function () {
    $content = "%PDF-1.7\nrelatório técnico";
    $document = new TravelReportDocument(
        externalMessageId: 'message-001',
        originalFileName: 'relatorio.pdf',
        mimeType: 'application/pdf',
        content: $content,
        maxSizeBytes: 1024,
    );

    expect($document->content())->toBe($content)
        ->and($document->sizeBytes)->toBe(strlen($content))
        ->and($document->mimeType)->toBe(TravelReportDocument::PdfMimeType);
});

it('rejects a document that is not a PDF or exceeds the configured size', function (string $content) {
    new TravelReportDocument(
        externalMessageId: 'message-001',
        originalFileName: 'relatorio.pdf',
        mimeType: 'application/pdf',
        content: $content,
        maxSizeBytes: 10,
    );
})->with([
    'invalid signature' => 'not a pdf',
    'too large' => "%PDF-1.7\nlarge content",
])->throws(InvalidArgumentException::class);

it('completes a draft only after all required metadata is collected', function () {
    $draft = TravelReportDraftEntity::start()
        ->withMunicipality(new Municipality('Salvador'))
        ->withProcess(new SeiProcess('012.3456.2026.0001234-00'))
        ->withTravelDate(TravelDate::fromBrazilianFormat('22/07/2026'))
        ->withResponsible(new ResponsibleName('João Silva'));

    $report = $draft->complete(
        id: 'report-001',
        externalMessageId: 'message-001',
        phone: '5571999999999',
        document: new TravelReportDocument(
            externalMessageId: 'message-001',
            originalFileName: 'relatorio.pdf',
            mimeType: 'application/pdf',
            content: "%PDF-1.7\ncontent",
        ),
        registeredAt: new DateTimeImmutable('2026-07-31 10:00:00'),
    );

    expect($report->id)->toBe('report-001')
        ->and($report->municipality->value)->toBe('Salvador')
        ->and($report->seiProcess?->value)->toBe('012.3456.2026.0001234-00')
        ->and($report->travelDate->formatted())->toBe('22/07/2026');
});

it('does not complete a draft before the document state', function () {
    TravelReportDraftEntity::start()->complete(
        id: 'report-001',
        externalMessageId: 'message-001',
        phone: '5571999999999',
        document: new TravelReportDocument(
            externalMessageId: 'message-001',
            originalFileName: 'relatorio.pdf',
            mimeType: 'application/pdf',
            content: "%PDF-1.7\ncontent",
        ),
        registeredAt: new DateTimeImmutable,
    );
})->throws(InvalidArgumentException::class, 'O relatório de viagem ainda não possui todos os metadados obrigatórios.');
