<?php

declare(strict_types=1);

namespace App\Core\Infra\Service;

use App\Core\Application\Interfaces\Service\WhatsappDefaultRepliesInterface;
use App\Core\Application\Interfaces\Service\WhatsappFoundRecordsReplyBuilderInterface;
use App\Core\Application\Interfaces\Service\WhatsappMessageResponseFormatterInterface;
use App\Core\Infra\Message\WhatsappResponsePayloadFactory;

class WhatsappMessageResponseFormatter implements WhatsappMessageResponseFormatterInterface
{
    /**
     * @param  iterable<WhatsappFoundRecordsReplyBuilderInterface>  $foundRecordsReplyBuilders
     */
    public function __construct(
        private readonly WhatsappDefaultRepliesInterface $defaultReplies,
        private readonly WhatsappResponsePayloadFactory $payloadFactory,
        private readonly iterable $foundRecordsReplyBuilders,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @param  array{term: string|null, total: int, data: list<array<string, mixed>>}  $result
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function format(string $intent, array $filters, array $result): array
    {
        $reply = $result['total'] === 0
            ? $this->defaultReplies->noRecords()
            : $this->foundRecordsReply($intent, $filters, $result);

        return $this->payloadFactory->withRecords($intent, $filters, $result, $reply);
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function greeting(): array
    {
        return $this->emptyResponse(
            intent: 'greeting',
            reply: $this->defaultReplies->greeting(),
        );
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function unknownIntent(): array
    {
        return $this->emptyResponse(
            intent: 'unknown',
            reply: $this->defaultReplies->unknownIntent(),
        );
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function unsupportedMessageContent(): array
    {
        return $this->emptyResponse(
            intent: 'unsupported_message_content',
            reply: $this->defaultReplies->unsupportedMessageContent(),
        );
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function rateLimited(): array
    {
        return $this->emptyResponse(
            intent: 'rate_limited',
            reply: $this->defaultReplies->rateLimited(),
        );
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function dataSourceUnavailable(): array
    {
        return $this->emptyResponse(
            intent: 'data_source_unavailable',
            reply: $this->defaultReplies->dataSourceUnavailable(),
        );
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function error(): array
    {
        return $this->emptyResponse(
            intent: 'error',
            reply: $this->defaultReplies->error(),
        );
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    private function emptyResponse(string $intent, string $reply): array
    {
        return $this->payloadFactory->empty($intent, $reply);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  array{term: string|null, total: int, data: list<array<string, mixed>>}  $result
     */
    private function foundRecordsReply(string $intent, array $filters, array $result): string
    {
        foreach ($this->foundRecordsReplyBuilders as $builder) {
            if ($builder->supports($intent)) {
                return $builder->build($filters, $result);
            }
        }

        return sprintf(
            'Encontrei %d %s para essa consulta.',
            $result['total'],
            $result['total'] === 1 ? 'registro' : 'registros',
        );
    }
}
