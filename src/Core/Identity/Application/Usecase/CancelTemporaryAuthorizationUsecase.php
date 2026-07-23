<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\Usecase;

use App\Core\Identity\Application\DTO\CancelTemporaryAuthorizationInputDTO;
use App\Core\Identity\Application\DTO\TemporaryAuthorizationOutputDTO;
use App\Core\Identity\Application\Interface\ClockInterface;
use App\Core\Identity\Application\Interface\Service\TemporaryAuthorizationFinderServiceInterface;
use App\Core\Identity\Application\Interface\Service\TemporaryAuthorizationGuardServiceInterface;
use App\Core\Identity\Application\Interface\Usecase\CancelTemporaryAuthorizationUsecaseInterface;
use App\Core\Identity\Application\Trait\ToTemporaryAuthorizationOutputTrait;
use App\Core\Identity\Domain\Repository\TemporaryAuthorizationRepositoryInterface;
use App\Core\Identity\Enum\TemporaryAuthorizationStatusEnum;

class CancelTemporaryAuthorizationUsecase implements CancelTemporaryAuthorizationUsecaseInterface
{
    use ToTemporaryAuthorizationOutputTrait;

    public function __construct(
        private readonly TemporaryAuthorizationFinderServiceInterface $finder,
        private readonly TemporaryAuthorizationGuardServiceInterface $guard,
        private readonly TemporaryAuthorizationRepositoryInterface $temporaryAuthorizationRepository,
        private readonly ClockInterface $clock,
    ) {}

    public function __invoke(CancelTemporaryAuthorizationInputDTO $input): TemporaryAuthorizationOutputDTO
    {
        $now = $this->clock->now();
        $authorization = $this->finder->findOrFail($input->authorizationId);

        $this->guard->assertContextMatches($authorization, $input->context);
        $this->guard->assertProtectedActionMatches($authorization, $input->protectedAction);
        $this->guard->assertNotExpired($authorization, $now);

        if ($authorization->status === TemporaryAuthorizationStatusEnum::Cancelled) {
            return $this->toOutput($authorization);
        }

        $this->guard->assertCancellable($authorization);

        return $this->toOutput($this->temporaryAuthorizationRepository->save($authorization->cancel($now)));
    }
}
