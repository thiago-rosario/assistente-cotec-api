<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\Usecase;

use App\Core\Identity\Application\DTO\TemporaryAuthorizationOutputDTO;
use App\Core\Identity\Application\DTO\ValidateTemporaryAuthorizationInputDTO;
use App\Core\Identity\Application\Interface\ClockInterface;
use App\Core\Identity\Application\Interface\Service\TemporaryAuthorizationFinderServiceInterface;
use App\Core\Identity\Application\Interface\Service\TemporaryAuthorizationGuardServiceInterface;
use App\Core\Identity\Application\Interface\Usecase\ValidateTemporaryAuthorizationUsecaseInterface;
use App\Core\Identity\Application\Trait\ToTemporaryAuthorizationOutputTrait;

class ValidateTemporaryAuthorizationUsecase implements ValidateTemporaryAuthorizationUsecaseInterface
{
    use ToTemporaryAuthorizationOutputTrait;

    public function __construct(
        private readonly TemporaryAuthorizationFinderServiceInterface $finder,
        private readonly TemporaryAuthorizationGuardServiceInterface $guard,
        private readonly ClockInterface $clock,
    ) {}

    public function __invoke(ValidateTemporaryAuthorizationInputDTO $input): TemporaryAuthorizationOutputDTO
    {
        $now = $this->clock->now();
        $authorization = $this->finder->findOrFail($input->authorizationId);

        $this->guard->assertNotExpired($authorization, $now);
        $this->guard->assertAuthorizedForExecution($authorization, $now);
        $this->guard->assertContextMatches($authorization, $input->context);
        $this->guard->assertProtectedActionMatches($authorization, $input->protectedAction);

        return $this->toOutput($authorization);
    }
}
