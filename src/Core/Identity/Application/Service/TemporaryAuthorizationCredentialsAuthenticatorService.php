<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\Service;

use App\Core\Identity\Application\Interface\Service\TemporaryAuthorizationCredentialsAuthenticatorServiceInterface;
use App\Core\Identity\Domain\Entity\TemporaryAuthorizationEntity;
use App\Core\Identity\Domain\Entity\UserEntity;
use App\Core\Identity\Domain\Repository\TemporaryAuthorizationRepositoryInterface;
use App\Core\Identity\Domain\Repository\UserRepositoryInterface;
use App\Core\Identity\Domain\Service\CredentialsVerifierInterface;
use App\Core\Identity\Domain\ValueObject\Login;
use App\Core\Identity\Enum\IdentityCodeExceptionEnum;
use App\Core\Identity\Exception\IdentityApplicationException;
use DateTimeImmutable;

class TemporaryAuthorizationCredentialsAuthenticatorService implements TemporaryAuthorizationCredentialsAuthenticatorServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly CredentialsVerifierInterface $credentialsVerifier,
        private readonly TemporaryAuthorizationRepositoryInterface $temporaryAuthorizationRepository,
    ) {}

    public function authenticateOrFail(TemporaryAuthorizationEntity $authorization, Login $login, #[\SensitiveParameter] string $password, DateTimeImmutable $now): UserEntity
    {
        $repositoryUser = $this->userRepository->findByLogin($login);
        $verifiedUser = $this->credentialsVerifier->verify($login, $password);

        if ($this->credentialsBelongToRepositoryUser($repositoryUser, $verifiedUser)) {
            return $verifiedUser;
        }

        $this->recordInvalidCredentials($authorization, $now);
    }

    private function credentialsBelongToRepositoryUser(?UserEntity $repositoryUser, ?UserEntity $verifiedUser): bool
    {
        return $repositoryUser !== null
            && $verifiedUser !== null
            && $verifiedUser->id !== null
            && $repositoryUser->isSameUser($verifiedUser->id);
    }

    private function recordInvalidCredentials(TemporaryAuthorizationEntity $authorization, DateTimeImmutable $now): never
    {
        $updatedAuthorization = $authorization->recordFailedAttempt($now);
        $this->temporaryAuthorizationRepository->save($updatedAuthorization);

        if ($updatedAuthorization->hasAttemptsExceeded()) {
            throw new IdentityApplicationException(IdentityCodeExceptionEnum::TemporaryAuthorizationAttemptsExceeded);
        }

        throw new IdentityApplicationException(IdentityCodeExceptionEnum::InvalidTemporaryAuthorizationCredentials);
    }
}
