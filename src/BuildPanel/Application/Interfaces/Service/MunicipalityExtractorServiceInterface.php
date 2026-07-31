<?php

declare(strict_types=1);

namespace App\BuildPanel\Application\Interfaces\Service;

interface MunicipalityExtractorServiceInterface
{
    public function extract(string $message): ?string;
}
