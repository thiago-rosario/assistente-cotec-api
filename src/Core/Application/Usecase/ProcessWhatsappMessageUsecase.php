<?php

declare(strict_types=1);

namespace App\Core\Application\Usecase;

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Interfaces\Service\BuildPanelWhatsappMessageServiceInterface;
use App\Core\Application\Interfaces\Service\GreetingMessageMatcherServiceInterface;
use App\Core\Application\Interfaces\Service\WhatsappMessageResponseFormatterInterface;
use App\Core\Application\Interfaces\Usecase\ProcessWhatsappMessageUsecaseInterface;
use Google\Service\Exception as GoogleServiceException;
use GuzzleHttp\Exception\ConnectException;
use OpenAI\Exceptions\RateLimitException;
use Throwable;

class ProcessWhatsappMessageUsecase implements ProcessWhatsappMessageUsecaseInterface
{
    public function __construct(
        private readonly GreetingMessageMatcherServiceInterface $greetingMatcher,
        private readonly BuildPanelWhatsappMessageServiceInterface $buildPanel,
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

            return $this->buildPanel->process($input->message);
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
