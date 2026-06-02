<?php

declare(strict_types=1);

namespace App\Core\Application\Usecase;

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Interfaces\InterpretWhatsappMessageWithAiServiceInterface;
use App\Core\Application\Interfaces\ProcessWhatsappMessageUsecaseInterface;
use App\Core\Application\Interfaces\WhatsappMessageInterpretationParserInterface;
use App\Core\Application\Interfaces\WhatsappMessageResponseFormatterInterface;
use App\Core\Application\Interfaces\WhatsappMessageSearchAdapterInterface;
use Throwable;

class ProcessWhatsappMessageUsecase implements ProcessWhatsappMessageUsecaseInterface
{
    public function __construct(
        private readonly InterpretWhatsappMessageWithAiServiceInterface $aiInterpreter,
        private readonly WhatsappMessageInterpretationParserInterface $interpretationParser,
        private readonly WhatsappMessageSearchAdapterInterface $searchAdapter,
        private readonly WhatsappMessageResponseFormatterInterface $responseFormatter,
    ) {}

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function __invoke(ReceivedMessageInputDTO $input): array
    {
        try {
            $interpretation = $this->interpretationParser->parse(
                ($this->aiInterpreter)($input->message),
            );

            if ($interpretation['intent'] === 'unknown') {
                return $this->responseFormatter->unknownIntent();
            }

            $result = $this->searchAdapter->search(
                $interpretation['intent'],
                $interpretation['filters'],
            );

            return $this->responseFormatter->format(
                $interpretation['intent'],
                $interpretation['filters'],
                $result,
            );
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->responseFormatter->error();
        }
    }
}
