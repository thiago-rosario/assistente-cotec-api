<?php

declare(strict_types=1);

namespace App\Contract\Application\Trait;

use App\Contract\Application\DTO\ContractExecutionDeadlineOutputDTO;
use App\Contract\Application\DTO\ContractReadjustmentOutputDTO;
use App\Contract\Application\DTO\ValueAdditiveOutputDTO;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;

trait GroupsContractSummaryDataTrait
{
    /**
     * @param array<string, array{
     *     contractNumber: ContractNumberValueObject,
     *     company: ?string,
     *     seiProcess: ?string,
     *     municipalities: list<string>,
     *     valueAdditives: list<ValueAdditiveOutputDTO>,
     *     readjustments: list<ContractReadjustmentOutputDTO>,
     *     executionDeadlines: list<ContractExecutionDeadlineOutputDTO>
     * }> $contractsByNumber
     */
    private function ensureContractGroup(
        array &$contractsByNumber,
        ContractNumberValueObject $contractNumber,
        ?string $company,
    ): void {
        $contractKey = $this->contractKey($contractNumber->value);

        if (! isset($contractsByNumber[$contractKey])) {
            $contractsByNumber[$contractKey] = [
                'contractNumber' => $contractNumber,
                'company' => $this->nullableValue($company),
                'seiProcess' => null,
                'municipalities' => [],
                'valueAdditives' => [],
                'readjustments' => [],
                'executionDeadlines' => [],
            ];

            return;
        }

        if ($contractsByNumber[$contractKey]['company'] === null) {
            $contractsByNumber[$contractKey]['company'] = $this->nullableValue($company);
        }
    }

    /**
     * @param  list<string>  $municipalities
     */
    private function addMunicipality(
        array &$municipalities,
        ?string $municipality,
    ): void {
        $municipality = $this->nullableValue($municipality);

        if (
            $municipality !== null
            && ! in_array($municipality, $municipalities, true)
        ) {
            $municipalities[] = $municipality;
        }
    }

    private function addSeiProcess(
        ?string &$currentSeiProcess,
        ?string $seiProcess,
    ): void {
        $currentSeiProcess ??= $this->nullableValue($seiProcess);
    }

    private function nullableValue(?string $value): ?string
    {
        $value = $value === null ? null : trim($value);

        return $value === '' || $value === '-'
            ? null
            : $value;
    }

    private function contractKey(string $contractNumber): string
    {
        $normalizedContractNumber = (new ContractNumberValueObject($contractNumber))->value;

        $parts = explode('/', $normalizedContractNumber, 2);
        $parts[0] = ltrim($parts[0], '0') ?: '0';

        return implode('/', $parts);
    }
}
