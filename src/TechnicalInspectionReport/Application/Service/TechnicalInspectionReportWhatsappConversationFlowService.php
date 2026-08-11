<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Application\Service;

use App\Core\Application\Interfaces\Message\WhatsappMainMenuMessageBuilderInterface;
use App\Core\Domain\Entity\MessageEntity;
use App\Core\Domain\Repository\WhatsappConversationStateRepositoryInterface;
use App\Core\Enum\WhatsappConversationState;
use App\TechnicalInspectionReport\Application\DTO\SearchTechnicalInspectionReportCatalogInputDTO;
use App\TechnicalInspectionReport\Application\DTO\StoreTechnicalInspectionReportInputDTO;
use App\TechnicalInspectionReport\Application\DTO\TechnicalInspectionReportDraftDTO;
use App\TechnicalInspectionReport\Application\Factory\TechnicalInspectionReportDraftFactory;
use App\TechnicalInspectionReport\Application\Interfaces\Builder\TechnicalInspectionReportDraftBuilderInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Service\TechnicalInspectionReportWhatsappConversationFlowServiceInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Storage\TechnicalInspectionReportDocumentTemporaryStorageInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Usecase\FindTechnicalInspectionReportUsecaseInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Usecase\StoreTechnicalInspectionReportUsecaseInterface;
use App\TechnicalInspectionReport\Domain\Repository\TechnicalInspectionReportDraftRepositoryInterface;
use App\TechnicalInspectionReport\Domain\ValueObject\InspectionDateValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\MunicipalityValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\ResponsiblePersonValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\SeiProcessValueObject;
use App\TechnicalInspectionReport\Exception\InvalidInspectionDateException;
use App\TechnicalInspectionReport\Exception\InvalidMunicipalityException;
use App\TechnicalInspectionReport\Exception\InvalidResponsiblePersonException;
use App\TechnicalInspectionReport\Exception\InvalidSeiProcessException;
use App\TechnicalInspectionReport\Exception\InvalidTechnicalInspectionReportFileException;
use App\TechnicalInspectionReport\Infra\Message\TechnicalInspectionReportWhatsappMessageBuilder;
use Throwable;

final class TechnicalInspectionReportWhatsappConversationFlowService implements TechnicalInspectionReportWhatsappConversationFlowServiceInterface
{
    public function __construct(
        private readonly WhatsappConversationStateRepositoryInterface $conversationStates,
        private readonly TechnicalInspectionReportDraftRepositoryInterface $drafts,
        private readonly TechnicalInspectionReportDocumentTemporaryStorageInterface $temporaryStorage,
        private readonly FindTechnicalInspectionReportUsecaseInterface $findReports,
        private readonly StoreTechnicalInspectionReportUsecaseInterface $storeReport,
        private readonly TechnicalInspectionReportDraftFactory $draftFactory,
        private readonly TechnicalInspectionReportDraftBuilderInterface $draftBuilder,
        private readonly TechnicalInspectionReportWhatsappMessageBuilder $messages,
        private readonly WhatsappMainMenuMessageBuilderInterface $mainMenu,
    ) {}

    public function start(MessageEntity $message): array
    {
        $this->clearDraft($message);
        $this->conversationStates->put($message, WhatsappConversationState::TechnicalInspectionReportMenu);

        return $this->messages->menu();
    }

    public function searchByMunicipality(MessageEntity $message, string $municipality): array
    {
        try {
            $municipalityValue = new MunicipalityValueObject($municipality);
        } catch (InvalidMunicipalityException) {
            return $this->messages->invalidSearchMunicipality();
        }

        $reports = ($this->findReports)(new SearchTechnicalInspectionReportCatalogInputDTO(
            municipality: $municipalityValue->value(),
        ));

        $this->conversationStates->put($message, WhatsappConversationState::TechnicalInspectionReportMenu);

        return $this->messages->consultationResults($municipalityValue->value(), $reports);
    }

    public function respondTo(MessageEntity $message): array
    {
        $state = $this->conversationStates->get($message);

        if ($state === null || ! $state->isTechnicalInspectionReport()) {
            return $this->mainMenu->mainMenu();
        }

        if ($state === WhatsappConversationState::TechnicalInspectionReportMenu) {
            return $this->menuOption($message);
        }

        if ($this->isCancellation($message)) {
            return $this->cancel($message);
        }

        if ($state === WhatsappConversationState::TechnicalInspectionReportProcessing) {
            return $this->messages->processing();
        }

        if ($state === WhatsappConversationState::TechnicalInspectionReportAwaitingSearchMunicipality) {
            return $this->searchMunicipality($message);
        }

        $draft = $this->drafts->get($message);

        if ($draft === null) {
            $this->conversationStates->put($message, WhatsappConversationState::MainMenu);

            return $this->messages->expired();
        }

        return match ($state) {
            WhatsappConversationState::TechnicalInspectionReportAwaitingMunicipality => $this->municipality($message, $draft),
            WhatsappConversationState::TechnicalInspectionReportAwaitingSeiDecision => $this->seiDecision($message, $draft),
            WhatsappConversationState::TechnicalInspectionReportAwaitingSeiProcess => $this->seiProcess($message, $draft),
            WhatsappConversationState::TechnicalInspectionReportAwaitingInspectionDate => $this->inspectionDate($message, $draft),
            WhatsappConversationState::TechnicalInspectionReportAwaitingResponsible => $this->responsiblePerson($message, $draft),
            WhatsappConversationState::TechnicalInspectionReportAwaitingDocument => $this->document($message, $draft),
            WhatsappConversationState::TechnicalInspectionReportAwaitingConfirmation => $this->confirmation($message, $draft),
            WhatsappConversationState::TechnicalInspectionReportRecoverableFailure => $this->recoverableFailure($message, $draft),
            WhatsappConversationState::TechnicalInspectionReportMenu,
            WhatsappConversationState::TechnicalInspectionReportCompleted => $this->messages->start(),
            default => $this->messages->expired(),
        };
    }

    private function menuOption(MessageEntity $message): array
    {
        return match ($message->normalizedContent()) {
            '1' => $this->startRegistration($message),
            '2' => $this->startConsultation($message),
            '0', 'menu', 'voltar' => $this->returnToMainMenu($message),
            default => $this->messages->invalidMenuOption(),
        };
    }

    private function startRegistration(MessageEntity $message): array
    {
        $this->clearDraft($message);
        $draft = $this->draftFactory->start($message);
        $this->drafts->put($message, $draft);
        $this->conversationStates->put(
            $message,
            WhatsappConversationState::TechnicalInspectionReportAwaitingMunicipality,
        );

        return $this->messages->start();
    }

    private function startConsultation(MessageEntity $message): array
    {
        $this->clearDraft($message);
        $this->conversationStates->put(
            $message,
            WhatsappConversationState::TechnicalInspectionReportAwaitingSearchMunicipality,
        );

        return $this->messages->consultationMunicipality();
    }

    private function municipality(MessageEntity $message, TechnicalInspectionReportDraftDTO $draft): array
    {
        try {
            $municipality = new MunicipalityValueObject($message->content());
        } catch (InvalidMunicipalityException) {
            return $this->messages->invalidMunicipality();
        }

        $draft = $this->draftBuilder->from($draft)
            ->withMunicipality($municipality->value())
            ->build();
        $this->drafts->put($message, $draft);
        $this->conversationStates->put($message, WhatsappConversationState::TechnicalInspectionReportAwaitingSeiDecision);

        return $this->messages->seiDecision();
    }

    private function seiDecision(MessageEntity $message, TechnicalInspectionReportDraftDTO $draft): array
    {
        return match ($message->normalizedContent()) {
            '1', 'nao', 'n', 'sem processo', 'sem processo sei', 'nao possui processo' => $this->skipSeiProcess($message, $draft),
            'sim', 's', '2' => $this->askSeiProcess($message, $draft),
            default => $this->seiProcess($message, $draft),
        };
    }

    private function askSeiProcess(MessageEntity $message, TechnicalInspectionReportDraftDTO $draft): array
    {
        $draft = $this->draftBuilder->from($draft)
            ->awaitingSeiProcess()
            ->build();
        $this->drafts->put($message, $draft);
        $this->conversationStates->put($message, WhatsappConversationState::TechnicalInspectionReportAwaitingSeiProcess);

        return $this->messages->seiProcess();
    }

    private function skipSeiProcess(MessageEntity $message, TechnicalInspectionReportDraftDTO $draft): array
    {
        $draft = $this->draftBuilder->from($draft)
            ->withoutSeiProcess()
            ->build();
        $this->drafts->put($message, $draft);
        $this->conversationStates->put($message, WhatsappConversationState::TechnicalInspectionReportAwaitingInspectionDate);

        return $this->messages->inspectionDate();
    }

    private function seiProcess(MessageEntity $message, TechnicalInspectionReportDraftDTO $draft): array
    {
        try {
            $seiProcess = new SeiProcessValueObject($message->content());
        } catch (InvalidSeiProcessException) {
            return $this->messages->invalidSeiProcess();
        }

        $draft = $this->draftBuilder->from($draft)
            ->withSeiProcess($seiProcess->value())
            ->build();
        $this->drafts->put($message, $draft);
        $this->conversationStates->put($message, WhatsappConversationState::TechnicalInspectionReportAwaitingInspectionDate);

        return $this->messages->inspectionDate();
    }

    private function searchMunicipality(MessageEntity $message): array
    {
        return $this->searchByMunicipality($message, $message->content());
    }

    private function inspectionDate(MessageEntity $message, TechnicalInspectionReportDraftDTO $draft): array
    {
        try {
            $inspectionDate = InspectionDateValueObject::fromBrazilianFormat($message->content());
        } catch (InvalidInspectionDateException) {
            return $this->messages->invalidInspectionDate();
        }

        $draft = $this->draftBuilder->from($draft)
            ->withInspectionDate($inspectionDate->formatted())
            ->build();
        $this->drafts->put($message, $draft);
        $this->conversationStates->put($message, WhatsappConversationState::TechnicalInspectionReportAwaitingResponsible);

        return $this->messages->responsiblePerson();
    }

    private function responsiblePerson(MessageEntity $message, TechnicalInspectionReportDraftDTO $draft): array
    {
        try {
            $responsiblePerson = new ResponsiblePersonValueObject($message->content());
        } catch (InvalidResponsiblePersonException) {
            return $this->messages->invalidResponsiblePerson();
        }

        $draft = $this->draftBuilder->from($draft)
            ->withResponsiblePerson($responsiblePerson->value())
            ->build();
        $this->drafts->put($message, $draft);
        $this->conversationStates->put($message, WhatsappConversationState::TechnicalInspectionReportAwaitingDocument);

        return $this->messages->document();
    }

    private function document(MessageEntity $message, TechnicalInspectionReportDraftDTO $draft): array
    {
        $document = $message->document();

        if ($document === null) {
            return $this->messages->invalidDocument();
        }

        try {
            $temporaryFile = $this->temporaryStorage->store($document, $draft->reportId);
        } catch (InvalidTechnicalInspectionReportFileException $exception) {
            return $this->messages->invalidDocument($exception->getMessage());
        } catch (Throwable) {
            $this->conversationStates->put($message, WhatsappConversationState::TechnicalInspectionReportRecoverableFailure);

            return $this->messages->storageFailure($draft->reportId);
        }

        $draft = $this->draftBuilder->from($draft)
            ->withDocument(
                documentPath: $temporaryFile->path,
                documentName: $document->originalFileName(),
                documentMimeType: 'application/pdf',
                documentSizeBytes: $temporaryFile->sizeBytes,
            )
            ->build();
        $this->drafts->put($message, $draft);
        $this->conversationStates->put($message, WhatsappConversationState::TechnicalInspectionReportAwaitingConfirmation);

        return $this->messages->confirmation($draft);
    }

    private function confirmation(MessageEntity $message, TechnicalInspectionReportDraftDTO $draft): array
    {
        return match ($message->normalizedContent()) {
            'sim', 's', '1', 'confirmar', 'confirmo' => $this->store($message, $draft),
            'nao', 'n' => $this->cancel($message),
            default => $this->messages->confirmation($draft),
        };
    }

    private function recoverableFailure(MessageEntity $message, TechnicalInspectionReportDraftDTO $draft): array
    {
        return match ($message->normalizedContent()) {
            'sim', 's', '1', 'tentar novamente', 'retry' => $this->store($message, $draft),
            'nao', 'n' => $this->cancel($message),
            default => $this->messages->storageFailure($draft->reportId),
        };
    }

    private function store(MessageEntity $message, TechnicalInspectionReportDraftDTO $draft): array
    {
        if ($draft->documentPath === null) {
            return $this->messages->expired();
        }

        $this->conversationStates->put($message, WhatsappConversationState::TechnicalInspectionReportProcessing);

        try {
            $report = $this->draftFactory->toEntity($draft);

            if (! $report->isComplete()) {
                return $this->messages->expired();
            }

            $report->markReadyForStorage()->beginStorage();
            $output = ($this->storeReport)(new StoreTechnicalInspectionReportInputDTO(
                report: $report,
                documentPath: $this->temporaryStorage->absolutePath($draft->documentPath),
            ));
            $report->confirmStorage();
        } catch (Throwable) {
            $this->conversationStates->put($message, WhatsappConversationState::TechnicalInspectionReportRecoverableFailure);

            return $this->messages->storageFailure($draft->reportId);
        }

        $this->clearDraft($message);
        $this->conversationStates->put($message, WhatsappConversationState::TechnicalInspectionReportMenu);

        return $this->messages->stored($output->report, $output->storedFile->webViewLink);
    }

    private function cancel(MessageEntity $message): array
    {
        $this->clearDraft($message);
        $this->conversationStates->put($message, WhatsappConversationState::TechnicalInspectionReportMenu);

        return $this->messages->cancelled();
    }

    private function returnToMainMenu(MessageEntity $message): array
    {
        $this->clearDraft($message);
        $this->conversationStates->put($message, WhatsappConversationState::MainMenu);

        return $this->mainMenu->mainMenu();
    }

    private function clearDraft(MessageEntity $message): void
    {
        $draft = $this->drafts->get($message);

        if ($draft?->documentPath !== null) {
            try {
                $this->temporaryStorage->delete($draft->documentPath);
            } catch (Throwable) {
            }
        }

        $this->drafts->forget($message);
    }

    private function isCancellation(MessageEntity $message): bool
    {
        return ! $message->hasDocument() && in_array($message->normalizedContent(), [
            '0',
            'cancelar',
            'menu',
            'voltar',
        ], true);
    }
}
