<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\Usecase;

use App\Core\Identity\Application\DTO\StartTemporaryAuthorizationInputDTO;
use App\Core\Identity\Application\DTO\TemporaryAuthorizationOutputDTO;
use App\Core\Identity\Application\Interface\ClockInterface;
use App\Core\Identity\Application\Interface\Usecase\StartTemporaryAuthorizationUsecaseInterface;
use App\Core\Identity\Application\Trait\ToTemporaryAuthorizationOutputTrait;
use App\Core\Identity\Domain\Entity\TemporaryAuthorizationEntity;
use App\Core\Identity\Domain\Policy\TemporaryAuthorizationPolicy;
use App\Core\Identity\Domain\Policy\TemporaryAuthorizationStatusPolicy;
use App\Core\Identity\Domain\Repository\TemporaryAuthorizationRepositoryInterface;

class StartTemporaryAuthorizationUsecase implements StartTemporaryAuthorizationUsecaseInterface
{
    use ToTemporaryAuthorizationOutputTrait;

    public function __construct(
        private readonly TemporaryAuthorizationRepositoryInterface $temporaryAuthorizationRepository,
        private readonly TemporaryAuthorizationPolicy $temporaryAuthorizationPolicy,
        private readonly ClockInterface $clock,
    ) {}

    public function __invoke(StartTemporaryAuthorizationInputDTO $input): TemporaryAuthorizationOutputDTO
    {
        $now = $this->clock->now();
        $activeAuthorization = $this->temporaryAuthorizationRepository->findActiveByContext(
            context: $input->context,
            protectedAction: $input->protectedAction,
            now: $now,
        );

        if ($activeAuthorization !== null && $this->temporaryAuthorizationPolicy->shouldExpire($activeAuthorization, $now)) {
            $this->temporaryAuthorizationRepository->save($activeAuthorization->expire($now));
            $activeAuthorization = null;
        }

        if ($activeAuthorization !== null && ! TemporaryAuthorizationStatusPolicy::isTerminal($activeAuthorization->status)) {
            return $this->toOutput($activeAuthorization);
        }

        $authorization = TemporaryAuthorizationEntity::start(
            context: $input->context,
            protectedAction: $input->protectedAction,
            maxAttempts: $input->maxAttempts,
            timeToLive: $input->timeToLive,
            issuedAt: $now,
            authorizationId: $input->authorizationId,
        );

        return $this->toOutput($this->temporaryAuthorizationRepository->save($authorization));
    }
}
