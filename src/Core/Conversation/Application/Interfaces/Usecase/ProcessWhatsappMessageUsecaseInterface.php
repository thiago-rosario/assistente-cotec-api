<?php

declare(strict_types=1);

namespace App\Core\Conversation\Application\Interfaces\Usecase;

use App\Core\Conversation\Application\DTO\ReceivedMessageInputDTO;

interface ProcessWhatsappMessageUsecaseInterface
{
    public function __invoke(ReceivedMessageInputDTO $input): array;
}
