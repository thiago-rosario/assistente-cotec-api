<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Infra\Repository\DriveRepository;

use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportEntity;
use Illuminate\Support\Str;

class FindTechnicalInspectionReportByMunicipalityDriveRepository
{
    /**
     * @param  list<TechnicalInspectionReportEntity>  $reports
     * @return list<TechnicalInspectionReportEntity>
     */
    public function findByMunicipality(array $reports, string $municipality): array
    {
        return array_values(array_filter(
            $reports,
            fn (TechnicalInspectionReportEntity $report): bool => $report->municipality() !== null
                && $this->municipalitiesMatch($report->municipality()->value(), $municipality),
        ));
    }

    private function municipalitiesMatch(string $candidate, string $municipality): bool
    {
        $normalizedCandidate = $this->normalizeMunicipality($candidate);
        $normalizedMunicipality = $this->normalizeMunicipality($municipality);

        if ($normalizedCandidate === '' || $normalizedMunicipality === '') {
            return false;
        }

        if ($normalizedCandidate === $normalizedMunicipality) {
            return true;
        }

        $allowedDistance = match (true) {
            mb_strlen($normalizedMunicipality) >= 12 => 2,
            mb_strlen($normalizedMunicipality) >= 5 => 1,
            default => 0,
        };

        return $allowedDistance > 0
            && levenshtein($normalizedCandidate, $normalizedMunicipality) <= $allowedDistance;
    }

    private function normalizeMunicipality(string $value): string
    {
        return Str::of($value)
            ->trim()
            ->lower()
            ->ascii()
            ->replaceMatches('/\b(d[aeo]s?)\b/', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();
    }
}
