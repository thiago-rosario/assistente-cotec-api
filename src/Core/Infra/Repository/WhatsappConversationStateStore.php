<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository;

use App\Core\Application\DTO\WhatsappConversationStateDTO;
use App\Core\Application\Interfaces\Repository\WhatsappConversationStateStoreInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

final readonly class WhatsappConversationStateStore implements WhatsappConversationStateStoreInterface
{
    public function __construct(
        private CacheRepository $cache,
    ) {}

    public function get(?string $phone): ?WhatsappConversationStateDTO
    {
        $key = $this->key($phone);

        if ($key === null) {
            return null;
        }

        $state = $this->cache->get($key);

        if (! is_array($state) || ! is_string($state['route'] ?? null)) {
            return null;
        }

        return new WhatsappConversationStateDTO(
            route: $state['route'],
            municipality: is_string($state['municipality'] ?? null) ? $state['municipality'] : null,
            contractOption: is_int($state['contract_option'] ?? null)
                ? $state['contract_option']
                : null,
        );
    }

    public function put(?string $phone, WhatsappConversationStateDTO $state): void
    {
        $key = $this->key($phone);

        if ($key === null) {
            return;
        }

        $this->cache->put($key, [
            'route' => $state->route,
            'municipality' => $state->municipality,
            'contract_option' => $state->contractOption,
        ], $this->ttl());
    }

    public function forget(?string $phone): void
    {
        $key = $this->key($phone);

        if ($key !== null) {
            $this->cache->forget($key);
        }
    }

    private function key(?string $phone): ?string
    {
        $phone = is_string($phone) ? trim($phone) : '';

        return $phone === '' ? null : 'whatsapp:conversation:'.$phone;
    }

    private function ttl(): int
    {
        return (int) config('whatsapp.conversation_state_ttl', 1800);
    }
}
