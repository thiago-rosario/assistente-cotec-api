<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Domain\Repository;

use App\Core\Domain\Entity\MessageEntity;
use App\TechnicalInspectionReport\Application\DTO\TechnicalInspectionReportDraftDTO;

interface TechnicalInspectionReportDraftRepositoryInterface
{
    public function get(MessageEntity $message): ?TechnicalInspectionReportDraftDTO;

    public function put(MessageEntity $message, TechnicalInspectionReportDraftDTO $draft): void;

    public function forget(MessageEntity $message): void;
}
