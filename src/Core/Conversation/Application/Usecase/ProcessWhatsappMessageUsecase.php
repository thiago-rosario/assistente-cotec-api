<?php

declare(strict_types=1);

namespace App\Core\Conversation\Application\Usecase;

use App\Core\Conversation\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Conversation\Application\Interfaces\Adapter\WhatsappMessageSearchAdapterInterface;
use App\Core\Conversation\Application\Interfaces\Repository\ConversationStateRepositoryInterface;
use App\Core\Conversation\Application\Interfaces\Service\AcceptedWhatsappMessageInterpretationServiceInterface;
use App\Core\Conversation\Application\Interfaces\Service\GreetingMessageMatcherServiceInterface;
use App\Core\Conversation\Application\Interfaces\Service\ResolveWhatsappMessageInterpretationServiceInterface;
use App\Core\Conversation\Application\Interfaces\Service\WhatsappMessageResponseFormatterInterface;
use App\Core\Conversation\Application\Interfaces\Usecase\ProcessWhatsappMessageUsecaseInterface;
use App\Core\Conversation\Enum\ConversationState;
use App\Core\Conversation\Enum\WhatsappMessageIntentEnum;
use Google\Service\Exception as GoogleServiceException;
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
        private readonly AcceptedWhatsappMessageInterpretationServiceInterface $service,
        private readonly ConversationStateRepositoryInterface $conversationStateRepository,
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
                $this->conversationStateRepository->put($input, ConversationState::MainMenu);

                return $this->responseFormatter->greeting();
            }

            $state = $this->conversationStateRepository->get($input);
            $interpretation = $state === null
                ? ($this->resolveInterpretation)($input->message)
                : ($this->resolveInterpretation)($input->message, $state);

            if ($interpretation->intent === WhatsappMessageIntentEnum::OPEN_BUILD_PANEL->value) {
                $this->conversationStateRepository->put($input, ConversationState::BuildPanelConsultation);

                return $this->responseFormatter->buildPanelConsultation();
            }

            if ($interpretation->intent === WhatsappMessageIntentEnum::UNKNOWN->value) {
                return $this->responseFormatter->unknownIntent();
            }

            if (! $this->service->accepts($interpretation->intent, $interpretation->filters)) {
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
        } catch (GoogleServiceException $googleServiceException) {
            report($googleServiceException);

            return $this->responseFormatter->dataSourceUnavailable();
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->responseFormatter->error();
        }
    }
}
