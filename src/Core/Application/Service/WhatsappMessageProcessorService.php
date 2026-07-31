<?php

declare(strict_types=1);

namespace App\Core\Application\Service;

use App\BuildPanel\Application\Interfaces\Service\WhatsappMessageResponseFormatterInterface;
use App\Core\Application\Interfaces\Service\WhatsappConversationFlowServiceInterface;
use App\Core\Application\Interfaces\Service\WhatsappMessageProcessorInterface;
use App\Core\Domain\Entity\MessageEntity;
use Google\Service\Exception as GoogleServiceException;
use GuzzleHttp\Exception\ConnectException;
use OpenAI\Exceptions\RateLimitException;
use Throwable;

class WhatsappMessageProcessorService implements WhatsappMessageProcessorInterface
{
    public function __construct(
        private readonly WhatsappConversationFlowServiceInterface $flow,
        private readonly WhatsappMessageResponseFormatterInterface $responseFormatter,
    ) {}

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function process(MessageEntity $message): array
    {
        try {
            return $this->flow->respondTo($message);
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
