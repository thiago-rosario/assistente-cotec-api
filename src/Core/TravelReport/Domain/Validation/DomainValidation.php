<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Domain\Validation;

use App\Core\TravelReport\Exception\FileNameRequiredException;
use App\Core\TravelReport\Exception\FilePathRequiredException;
use App\Core\TravelReport\Exception\InvalidMunicipalityIdException;
use App\Core\TravelReport\Exception\SeiProcessRequiredException;
use App\Core\TravelReport\Exception\SubmittedByUserIdRequiredException;

final class DomainValidation
{
    public static function validateSubmittedByUserId(string $submittedByUserId): void
    {
        if (trim($submittedByUserId) === '') {
            throw new SubmittedByUserIdRequiredException;
        }
    }

    public static function validateFileName(string $fileName): void
    {
        if (trim($fileName) === '') {
            throw new FileNameRequiredException;
        }
    }

    public static function validateFilePath(string $filePath): void
    {
        if (trim($filePath) === '') {
            throw new FilePathRequiredException;
        }
    }

    public static function validateMunicipalityId(int $municipalityId): void
    {
        if ($municipalityId <= 0) {
            throw new InvalidMunicipalityIdException;
        }
    }

    public static function validateSeiProcess(string $seiProcess): void
    {
        if (trim($seiProcess) === '') {
            throw new SeiProcessRequiredException;
        }
    }
}
