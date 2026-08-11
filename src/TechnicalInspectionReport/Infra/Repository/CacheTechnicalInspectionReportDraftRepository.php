<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Infra\Repository;

use App\Core\Domain\Entity\MessageEntity;
use App\TechnicalInspectionReport\Application\DTO\TechnicalInspectionReportDraftDTO;
use App\TechnicalInspectionReport\Application\Interfaces\Mapper\TechnicalInspectionReportDraftMapperInterface;
use App\TechnicalInspectionReport\Domain\Repository\TechnicalInspectionReportDraftRepositoryInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

final class CacheTechnicalInspectionReportDraftRepository implements TechnicalInspectionReportDraftRepositoryInterface
{
    public function __construct(
        private readonly CacheRepository $cache,
        private readonly TechnicalInspectionReportDraftMapperInterface $mapper,
    ) {}

    public function get(MessageEntity $message): ?TechnicalInspectionReportDraftDTO
    {
        $key = $this->keyFor($message);

        if ($key === null) {
            return null;
        }

        $draft = $this->cache->get($key);

        return is_array($draft) ? $this->mapper->fromArray($draft) : null;
    }

    public function put(MessageEntity $message, TechnicalInspectionReportDraftDTO $draft): void
    {
        $key = $this->keyFor($message);

        if ($key !== null) {
            $this->cache->put($key, $this->mapper->toArray($draft), $this->ttl());
        }
    }

    public function forget(MessageEntity $message): void
    {
        $key = $this->keyFor($message);

        if ($key !== null) {
            $this->cache->forget($key);
        }
    }

    private function keyFor(MessageEntity $message): ?string
    {
        $phone = $message->normalizedPhone();

        return $phone === null ? null : 'whatsapp:technical-inspection-report:draft:'.$phone;
    }

    private function ttl(): int
    {
        return (int) config('technical_inspection_report.conversation_state_ttl', 86400);
    }
}
