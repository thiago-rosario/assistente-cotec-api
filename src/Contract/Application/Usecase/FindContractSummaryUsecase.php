<?php

declare(strict_types=1);

namespace App\Contract\Application\Usecase;

use App\Contract\Application\DTO\ContractExecutionDeadlineOutputDTO;
use App\Contract\Application\DTO\ContractReadjustmentOutputDTO;
use App\Contract\Application\DTO\FindContractSummaryOutputDTO;
use App\Contract\Application\DTO\MunicipalityContractReferenceDTO;
use App\Contract\Application\DTO\SearchContractInputDTO;
use App\Contract\Application\DTO\ValueAdditiveOutputDTO;
use App\Contract\Application\Interfaces\Assembly\ContractSummaryAssemblerInterface;
use App\Contract\Application\Interfaces\Usecase\FindContractAdjustmentsUsecaseInterface;
use App\Contract\Application\Interfaces\Usecase\FindContractExecutionDeadlineUsecaseInterface;
use App\Contract\Application\Interfaces\Usecase\FindContractSummaryUsecaseInterface;
use App\Contract\Application\Interfaces\Usecase\FindContractValueAdditivesUsecaseInterface;
use App\Contract\Application\Trait\CreatesContractEntityTrait;
use App\Contract\Application\Trait\GroupsContractSummaryDataTrait;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;
use App\Contract\Enum\ContractSearchTypeEnum;
use InvalidArgumentException;

class FindContractSummaryUsecase implements FindContractSummaryUsecaseInterface
{
    use CreatesContractEntityTrait;
    use GroupsContractSummaryDataTrait;

    public function __construct(
        private readonly FindContractValueAdditivesUsecaseInterface $valueAdditivesUsecase,
        private readonly FindContractAdjustmentsUsecaseInterface $adjustmentsUsecase,
        private readonly FindContractExecutionDeadlineUsecaseInterface $executionDeadlineUsecase,
        private readonly ContractSummaryAssemblerInterface $assembler,
    ) {}

    public function __invoke(SearchContractInputDTO $input): FindContractSummaryOutputDTO
    {
        $municipality = match ($input->searchType) {
            ContractSearchTypeEnum::Municipality => new MunicipalityValueObject($input->searchTerm),
            ContractSearchTypeEnum::ContractNumber => null,
            ContractSearchTypeEnum::Company,
            ContractSearchTypeEnum::SeiProcess => throw new InvalidArgumentException(
                'Contract summaries can only be searched by municipality or contract number.',
            ),
        };

        $valueAdditives = ($this->valueAdditivesUsecase)($input)->data;
        $adjustments = ($this->adjustmentsUsecase)($input)->data;
        $executionDeadlines = ($this->executionDeadlineUsecase)($input)->data;

        /** @var array<string, array{
         *     contractNumber: ContractNumberValueObject,
         *     company: ?string,
         *     seiProcess: ?string,
         *     municipalities: list<string>,
         *     valueAdditives: list<ValueAdditiveOutputDTO>,
         *     readjustments: list<ContractReadjustmentOutputDTO>,
         *     executionDeadlines: list<ContractExecutionDeadlineOutputDTO>
         * }> $contractsByNumber
         */
        $contractsByNumber = [];

        foreach ($valueAdditives as $valueAdditive) {
            $contractNumber = new ContractNumberValueObject($valueAdditive->contractNumber);
            $this->ensureContractGroup(
                $contractsByNumber,
                $contractNumber,
                $valueAdditive->company,
            );
            $contractsByNumber[$this->contractKey($contractNumber->value)]['valueAdditives'][] = $valueAdditive;
            $this->addMunicipality(
                $contractsByNumber[$this->contractKey($contractNumber->value)]['municipalities'],
                $valueAdditive->municipality,
            );
            $this->addSeiProcess(
                $contractsByNumber[$this->contractKey($contractNumber->value)]['seiProcess'],
                $valueAdditive->seiProcess,
            );
        }

        foreach ($adjustments as $adjustmentGroup) {
            $contractNumber = new ContractNumberValueObject($adjustmentGroup->contractNumber);
            $this->ensureContractGroup(
                $contractsByNumber,
                $contractNumber,
                $adjustmentGroup->company,
            );

            foreach ($adjustmentGroup->data as $adjustment) {
                $contractsByNumber[$this->contractKey($contractNumber->value)]['readjustments'][] = $adjustment;
                $this->addSeiProcess(
                    $contractsByNumber[$this->contractKey($contractNumber->value)]['seiProcess'],
                    $adjustment->seiProcess,
                );
            }
        }

        foreach ($executionDeadlines as $executionDeadline) {
            $contractNumber = new ContractNumberValueObject($executionDeadline->contractNumber);
            $this->ensureContractGroup(
                $contractsByNumber,
                $contractNumber,
                $executionDeadline->company,
            );
            $contractsByNumber[$this->contractKey($contractNumber->value)]['executionDeadlines'][] = $executionDeadline;
            $this->addMunicipality(
                $contractsByNumber[$this->contractKey($contractNumber->value)]['municipalities'],
                $executionDeadline->municipality,
            );
            $this->addSeiProcess(
                $contractsByNumber[$this->contractKey($contractNumber->value)]['seiProcess'],
                $executionDeadline->seiProcess,
            );
        }

        $summaries = [];

        foreach ($contractsByNumber as $contractData) {
            $reference = new MunicipalityContractReferenceDTO(
                contractNumber: $contractData['contractNumber'],
                company: $contractData['company'],
            );

            $summaries[] = $this->assembler->assemble(
                contract: $this->contractEntity($contractData, $municipality),
                reference: $reference,
                municipality: $municipality?->value,
                valueAdditives: $contractData['valueAdditives'],
                readjustments: $contractData['readjustments'],
                executionDeadlines: $contractData['executionDeadlines'],
            );
        }

        return new FindContractSummaryOutputDTO(
            searchTerm: $input->searchTerm,
            searchType: $input->searchType,
            total: count($summaries),
            data: $summaries,
        );
    }
}
