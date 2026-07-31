<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Factory;

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Domain\Entity\MessageEntity;

interface MessageFactoryInterface
{
    public function fromReceivedInput(ReceivedMessageInputDTO $input): MessageEntity;
}
