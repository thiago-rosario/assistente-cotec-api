<?php

declare(strict_types=1);

namespace App\Core\Application\Usecase;

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Interfaces\GreetingMessageMatcherServiceInterface;
use App\Core\Application\Interfaces\ProcessWhatsappMessageUsecaseInterface;
use App\Core\Application\Interfaces\ResolveWhatsappMessageInterpretationServiceInterface;
use App\Core\Application\Interfaces\WhatsappMessageResponseFormatterInterface;
use App\Core\Application\Interfaces\WhatsappMessageSearchAdapterInterface;
use App\Core\Enum\WhatsappMessageIntentEnum;
use GuzzleHttp\Exception\ConnectException;
use OpenAI\Exceptions\RateLimitException;
use Throwable;

class ProcessWhatsappMessageUsecase implements ProcessWhatsappMessageUsecaseInterface
{
    public function __construct(
        private readonly GreetingMessageMatcherServiceInterface $greetingMatcher,
        private readonly ResolveWhatsappMessageInterpretationServiceInterface $resolveInterpretation,
        private readonly WhatsappMessageSearchAdapterInterface $searchAdapter,
        private readonly WhatsappMessageResponseFormatterInterface $responseFormatter,
    ) {}

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function __invoke(ReceivedMessageInputDTO $input): array
    {
        try {
            if (trim($input->message) === '') {
                return $this->responseFormatter->unsupportedMessageContent();
            }

            if ($this->greetingMatcher->matches($input->message)) {
                return $this->responseFormatter->greeting();
            }

            $interpretation = ($this->resolveInterpretation)($input->message);

            if ($interpretation->intent === WhatsappMessageIntentEnum::UNKNOWN->value) {
                return $this->responseFormatter->unknownIntent();
            }

            $result = $this->searchAdapter->search(
                $interpretation->intent,
                $interpretation->filters,
            );

            return $this->responseFormatter->format(
                $interpretation->intent,
                $interpretation->filters,
                $result,
            );
        } catch (RateLimitException) {
            return $this->responseFormatter->rateLimited();
        } catch (ConnectException) {
            return $this->responseFormatter->dataSourceUnavailable();
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->responseFormatter->error();
        }
    }
}
