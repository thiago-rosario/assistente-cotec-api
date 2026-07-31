<?php

declare(strict_types=1);

namespace App\Core\Application\Usecase;

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Interfaces\Factory\MessageFactoryInterface;
use App\Core\Application\Interfaces\Service\WhatsappMessageProcessorInterface;
use App\Core\Application\Interfaces\Usecase\ProcessWhatsappMessageUsecaseInterface;

class ProcessWhatsappMessageUsecase implements ProcessWhatsappMessageUsecaseInterface
{
    public function __construct(
        private readonly MessageFactoryInterface $messages,
        private readonly WhatsappMessageProcessorInterface $processor,
    ) {}

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function __invoke(ReceivedMessageInputDTO $input): array
    {
        return $this->processor->process(
            $this->messages->fromReceivedInput($input),
        );
    }
}
