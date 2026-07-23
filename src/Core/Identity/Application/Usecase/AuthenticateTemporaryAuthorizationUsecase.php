<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\Usecase;

use App\Core\Identity\Application\DTO\AuthenticateTemporaryAuthorizationInputDTO;
use App\Core\Identity\Application\DTO\TemporaryAuthorizationOutputDTO;
use App\Core\Identity\Application\Interface\ClockInterface;
use App\Core\Identity\Application\Interface\Service\TemporaryAuthorizationCredentialsAuthenticatorServiceInterface;
use App\Core\Identity\Application\Interface\Service\TemporaryAuthorizationFinderServiceInterface;
use App\Core\Identity\Application\Interface\Service\TemporaryAuthorizationGuardServiceInterface;
use App\Core\Identity\Application\Interface\Usecase\AuthenticateTemporaryAuthorizationUsecaseInterface;
use App\Core\Identity\Application\Trait\ToTemporaryAuthorizationOutputTrait;
use App\Core\Identity\Domain\Repository\TemporaryAuthorizationRepositoryInterface;

class AuthenticateTemporaryAuthorizationUsecase implements AuthenticateTemporaryAuthorizationUsecaseInterface
{
    use ToTemporaryAuthorizationOutputTrait;

    public function __construct(
        private readonly TemporaryAuthorizationFinderServiceInterface $finder,
        private readonly TemporaryAuthorizationGuardServiceInterface $guard,
        private readonly TemporaryAuthorizationCredentialsAuthenticatorServiceInterface $credentialsAuthenticator,
        private readonly TemporaryAuthorizationRepositoryInterface $temporaryAuthorizationRepository,
        private readonly ClockInterface $clock,
    ) {}

    public function __invoke(AuthenticateTemporaryAuthorizationInputDTO $input): TemporaryAuthorizationOutputDTO
    {
        $now = $this->clock->now();
        $authorization = $this->finder->findOrFail($input->authorizationId);

        $this->guard->assertCanAuthenticate($authorization, $input->context, $input->protectedAction, $now);

        $user = $this->credentialsAuthenticator->authenticateOrFail($authorization, $input->login, $input->plainPassword, $now);

        $authorized = $authorization->authorize($user, $now);

        $this->guard->assertAuthorized($authorized, $now);

        return $this->toOutput($this->temporaryAuthorizationRepository->save($authorized));
    }
}
