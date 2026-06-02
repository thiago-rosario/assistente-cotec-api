<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces;

use App\Core\Application\DTO\ReceivedMessageInputDTO;

interface ProcessWhatsappMessageUsecaseInterface
{
    public function __invoke(ReceivedMessageInputDTO $input): array;
}
