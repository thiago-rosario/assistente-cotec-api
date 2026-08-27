<?php

declare(strict_types=1);

namespace App\Contract\Application\Trait;

use App\Contract\Application\DTO\MunicipalityContractReferenceDTO;
use App\Contract\Domain\Entity\ContractEntity;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;

/**
 * Resolves the official contract records used to build a general extract.
 *
 * The composing use case provides the contract repository and municipality
 * resolver. This trait contains only lookup and contract-number equivalence
 * rules; aggregation and formatting remain outside it.
 */
trait LocatesContractSummaryRecordsTrait
{
    /**
     * @param  list<MunicipalityContractReferenceDTO>  $references
     * @return list<MunicipalityContractReferenceDTO>
     */
    private function uniqueReferences(array $references): array
    {
        $unique = [];

        foreach ($references as $reference) {
            $key = $reference->contractNumber->equivalenceKey();

            if (! isset($unique[$key]) || ($unique[$key]->company === null && $reference->company !== null)) {
                $unique[$key] = $reference;
            }
        }

        return array_values($unique);
    }

    private function findContract(ContractNumberValueObject $contractNumber): ?ContractEntity
    {
        foreach ($this->contractNumberCandidates($contractNumber) as $candidate) {
            $contract = $this->repository->findByContractNumber($candidate);

            if ($contract !== null) {
                return $contract;
            }
        }

        return null;
    }

    /**
     * @return list<ContractNumberValueObject>
     */
    private function contractNumberCandidates(ContractNumberValueObject $contractNumber): array
    {
        $candidates = [$contractNumber];
        $parts = explode('/', $contractNumber->value, 2);

        if (count($parts) !== 2 || ! ctype_digit($parts[0])) {
            return $candidates;
        }

        $number = ltrim($parts[0], '0') ?: '0';

        foreach (array_unique([
            $number,
            str_pad($number, 2, '0', STR_PAD_LEFT),
            str_pad($number, 3, '0', STR_PAD_LEFT),
        ]) as $formattedNumber) {
            $candidate = new ContractNumberValueObject("{$formattedNumber}/{$parts[1]}");

            if (! in_array(
                $candidate->value,
                array_map(static fn (ContractNumberValueObject $value): string => $value->value, $candidates),
                true,
            )) {
                $candidates[] = $candidate;
            }
        }

        return $candidates;
    }
}
