<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Application\Interfaces\Service;

use App\Core\Domain\Entity\MessageEntity;

interface TechnicalInspectionReportWhatsappConversationFlowServiceInterface
{
    public function start(MessageEntity $message): array;

    public function respondTo(MessageEntity $message): array;
}
