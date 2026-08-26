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
use App\Contract\Domain\Entity\ContractEntity;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;
use App\Contract\Enum\ContractSearchTypeEnum;
use InvalidArgumentException;

class FindContractSummaryUsecase implements FindContractSummaryUsecaseInterface
{
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
         *     currentSituation: ?string,
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
            $contractsByNumber[$contractNumber->value]['valueAdditives'][] = $valueAdditive;
            $this->addMunicipality(
                $contractsByNumber[$contractNumber->value]['municipalities'],
                $valueAdditive->municipality,
            );
            $this->addSeiProcess(
                $contractsByNumber[$contractNumber->value]['seiProcess'],
                $valueAdditive->seiProcess,
            );
            $this->addCurrentSituation(
                $contractsByNumber[$contractNumber->value]['currentSituation'],
                $valueAdditive->situation ?? $valueAdditive->status,
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
                $contractsByNumber[$contractNumber->value]['readjustments'][] = $adjustment;
                $this->addSeiProcess(
                    $contractsByNumber[$contractNumber->value]['seiProcess'],
                    $adjustment->seiProcess,
                );
                $this->addCurrentSituation(
                    $contractsByNumber[$contractNumber->value]['currentSituation'],
                    $adjustment->status,
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
            $contractsByNumber[$contractNumber->value]['executionDeadlines'][] = $executionDeadline;
            $this->addMunicipality(
                $contractsByNumber[$contractNumber->value]['municipalities'],
                $executionDeadline->municipality,
            );
            $this->addSeiProcess(
                $contractsByNumber[$contractNumber->value]['seiProcess'],
                $executionDeadline->seiProcess,
            );
            $contractsByNumber[$contractNumber->value]['currentSituation'] = $executionDeadline->contractSituation
                ?? $contractsByNumber[$contractNumber->value]['currentSituation'];
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

    /**
     * @param  array<string, array{
     *     contractNumber: ContractNumberValueObject,
     *     company: ?string,
     *     seiProcess: ?string,
     *     municipalities: list<string>,
     *     currentSituation: ?string,
     *     valueAdditives: list<ValueAdditiveOutputDTO>,
     *     readjustments: list<ContractReadjustmentOutputDTO>,
     *     executionDeadlines: list<ContractExecutionDeadlineOutputDTO>
     * }>  $contractsByNumber
     */
    private function ensureContractGroup(
        array &$contractsByNumber,
        ContractNumberValueObject $contractNumber,
        ?string $company,
    ): void {
        if (! isset($contractsByNumber[$contractNumber->value])) {
            $contractsByNumber[$contractNumber->value] = [
                'contractNumber' => $contractNumber,
                'company' => $this->nullableValue($company),
                'seiProcess' => null,
                'municipalities' => [],
                'currentSituation' => null,
                'valueAdditives' => [],
                'readjustments' => [],
                'executionDeadlines' => [],
            ];

            return;
        }

        if ($contractsByNumber[$contractNumber->value]['company'] === null) {
            $contractsByNumber[$contractNumber->value]['company'] = $this->nullableValue($company);
        }
    }

    /**
     * @param  array{
     *     contractNumber: ContractNumberValueObject,
     *     company: ?string,
     *     seiProcess: ?string,
     *     municipalities: list<string>,
     *     currentSituation: ?string,
     *     valueAdditives: list<ValueAdditiveOutputDTO>,
     *     readjustments: list<ContractReadjustmentOutputDTO>,
     *     executionDeadlines: list<ContractExecutionDeadlineOutputDTO>
     * }  $contractData
     */
    private function contractEntity(array $contractData, ?MunicipalityValueObject $municipality): ContractEntity
    {
        $deadline = $contractData['executionDeadlines'][0] ?? null;

        return new ContractEntity(
            contractNumber: $contractData['contractNumber']->value,
            company: $contractData['company'],
            seiProcess: $contractData['seiProcess'],
            municipalities: $municipality === null
                ? $contractData['municipalities']
                : [$municipality->value],
            validityEndDate: $deadline?->validityEndDate,
            executionDeadline: $deadline?->executionEndDate?->format('d/m/Y'),
            currentSituation: $contractData['currentSituation'],
        );
    }

    private function nullableValue(?string $value): ?string
    {
        $value = $value === null ? null : trim($value);

        return $value === '' || $value === '-' ? null : $value;
    }

    /**
     * @param  list<string>  $municipalities
     */
    private function addMunicipality(array &$municipalities, ?string $municipality): void
    {
        $municipality = $this->nullableValue($municipality);

        if ($municipality !== null && ! in_array($municipality, $municipalities, true)) {
            $municipalities[] = $municipality;
        }
    }

    private function addSeiProcess(?string &$currentSeiProcess, ?string $seiProcess): void
    {
        $currentSeiProcess ??= $this->nullableValue($seiProcess);
    }

    private function addCurrentSituation(?string &$currentSituation, ?string $situation): void
    {
        $currentSituation ??= $this->nullableValue($situation);
    }
}
