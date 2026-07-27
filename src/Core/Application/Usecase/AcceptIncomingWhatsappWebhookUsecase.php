<?php

declare(strict_types=1);

namespace App\Core\Application\Usecase;

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Interfaces\Adapter\WhatsappWebhookPayloadAdapterInterface;
use App\Core\Application\Interfaces\Usecase\AcceptIncomingWhatsappWebhookUsecaseInterface;
use App\Core\Application\Support\WhatsappLogContext;
use App\Jobs\ProcessIncomingWhatsappMessageJob;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Log;
use Throwable;

class AcceptIncomingWhatsappWebhookUsecase implements AcceptIncomingWhatsappWebhookUsecaseInterface
{
    public function __construct(
        private readonly CacheRepository $cache,
        private readonly Dispatcher $dispatcher,
        private readonly WhatsappWebhookPayloadAdapterInterface $adapter,
    ) {}

    /**
     * @return array{accepted: bool, external_id: string|null, duplicate: bool}
     */
    public function __invoke(ReceivedMessageInputDTO $input): array
    {
        Log::info('whatsapp_webhook_received', WhatsappLogContext::message(
            externalId: $input->externalId,
            phone: $input->phone,
            source: $input->source,
        ));

        $cacheKey = $this->cacheKey($input->externalId);

        if ($cacheKey !== null && ! $this->cache->add($cacheKey, 'queued', $this->idempotencyTtl())) {
            Log::info('whatsapp_message_duplicate', WhatsappLogContext::message(
                externalId: $input->externalId,
                phone: $input->phone,
                source: $input->source,
            ));

            return [
                'accepted' => false,
                'external_id' => $input->externalId,
                'duplicate' => true,
            ];
        }

        try {
            $this->dispatcher->dispatch(new ProcessIncomingWhatsappMessageJob(
                payload: $this->adapter->toArray($input),
            ));
        } catch (Throwable $throwable) {
            if ($cacheKey !== null) {
                $this->cache->forget($cacheKey);
            }

            throw $throwable;
        }

        Log::info('whatsapp_webhook_dispatched', WhatsappLogContext::message(
            externalId: $input->externalId,
            phone: $input->phone,
            source: $input->source,
        ));

        return [
            'accepted' => true,
            'external_id' => $input->externalId,
            'duplicate' => false,
        ];
    }

    private function cacheKey(?string $externalId): ?string
    {
        if ($externalId === null || trim($externalId) === '') {
            return null;
        }

        return 'whatsapp:incoming:'.trim($externalId);
    }

    private function idempotencyTtl(): int
    {
        return (int) config('whatsapp.incoming_idempotency_ttl', 86400);
    }
}
