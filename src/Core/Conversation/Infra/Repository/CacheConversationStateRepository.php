<?php

declare(strict_types=1);

namespace App\Core\Conversation\Infra\Repository;

use App\Core\Conversation\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Conversation\Application\Interfaces\Repository\ConversationStateRepositoryInterface;
use App\Core\Conversation\Enum\ConversationStateEnum;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final readonly class CacheConversationStateRepository implements ConversationStateRepositoryInterface
{
    private const string CachePrefix = 'whatsapp:conversation-state:';

    public function __construct(
        private CacheRepository $cache,
    ) {}

    public function get(ReceivedMessageInputDTO $input): ?ConversationStateEnum
    {
        $key = $this->key($input);

        if ($key === null) {
            return null;
        }

        $state = $this->cache->get($key);

        if (! is_string($state)) {
            return null;
        }

        return ConversationStateEnum::tryFrom($state);
    }

    public function put(ReceivedMessageInputDTO $input, ConversationStateEnum $state): void
    {
        $key = $this->key($input);

        if ($key === null) {
            return;
        }

        $this->cache->put($key, $state->value, now()->addHours(8));
    }

    public function forget(ReceivedMessageInputDTO $input): void
    {
        $key = $this->key($input);

        if ($key === null) {
            return;
        }

        $this->cache->forget($key);
    }

    private function key(ReceivedMessageInputDTO $input): ?string
    {
        $identifier = $input->phone
            ?? $input->senderName
            ?? $this->metadataIdentifier($input);

        if ($identifier === null) {
            return null;
        }

        return self::CachePrefix.hash('sha256', Str::lower($identifier));
    }

    private function metadataIdentifier(ReceivedMessageInputDTO $input): ?string
    {
        foreach ([
            'customer_contact',
            'from',
            'contact',
            'sender_phone',
            'last_message.customer_contact',
            'whatsapp_message.customer_contact',
        ] as $key) {
            $value = Arr::get($input->metadata, $key);

            if (! is_scalar($value)) {
                continue;
            }

            $value = trim((string) $value);

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }
}
