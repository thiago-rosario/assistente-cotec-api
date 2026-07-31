<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\EloquentRepository;

use App\Core\Domain\Entity\MessageEntity;
use App\Core\Domain\Repository\WhatsappConversationStateRepositoryInterface;
use App\Core\Enum\WhatsappConversationState;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class CacheWhatsappConversationStateRepository implements WhatsappConversationStateRepositoryInterface
{
    public function __construct(
        private readonly CacheRepository $cache,
    ) {}

    public function get(MessageEntity $message): ?WhatsappConversationState
    {
        $key = $this->keyFor($message);

        if ($key === null) {
            return null;
        }

        $state = $this->cache->get($key);

        return is_string($state) ? WhatsappConversationState::tryFrom($state) : null;
    }

    public function put(MessageEntity $message, WhatsappConversationState $state): void
    {
        $key = $this->keyFor($message);

        if ($key === null) {
            return;
        }

        $this->cache->put($key, $state->value, $this->ttl());
    }

    public function forget(MessageEntity $message): void
    {
        $key = $this->keyFor($message);

        if ($key === null) {
            return;
        }

        $this->cache->forget($key);
    }

    private function keyFor(MessageEntity $message): ?string
    {
        $phone = $message->normalizedPhone();

        return $phone === null ? null : 'whatsapp:conversation:'.$phone;
    }

    private function ttl(): int
    {
        return (int) config('whatsapp.conversation_state_ttl', 86400);
    }
}
