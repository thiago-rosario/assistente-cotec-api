<?php

declare(strict_types=1);

namespace App\Core\Application\Factory;

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Interfaces\Factory\MessageFactoryInterface;
use App\Core\Domain\Entity\MessageEntity;

class MessageFactory implements MessageFactoryInterface
{
    public function fromReceivedInput(ReceivedMessageInputDTO $input): MessageEntity
    {
        return new MessageEntity(
            content: $input->message,
            phone: $input->phone,
        );
    }
}
