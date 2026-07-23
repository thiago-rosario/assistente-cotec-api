<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\Usecase;

use App\Core\Identity\Application\DTO\CreateUserInputDTO;
use App\Core\Identity\Application\DTO\CreateUserOutputDTO;
use App\Core\Identity\Application\Interface\Usecase\CreateUserUsecaseInterface;
use App\Core\Identity\Domain\Entity\UserEntity;
use App\Core\Identity\Domain\Repository\UserRepositoryInterface;
use App\Core\Identity\Domain\Validation\UserDomainValidation;
use App\Core\Identity\Exception\PasswordConfirmationMismatchException;

class CreateUserUsecase implements CreateUserUsecaseInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function __invoke(CreateUserInputDTO $input): CreateUserOutputDTO
    {
        UserDomainValidation::validateEmail($input->email);
        UserDomainValidation::validatePassword($input->password);

        if ($input->password !== $input->passwordConfirmation) {
            throw new PasswordConfirmationMismatchException;
        }

        $user = UserEntity::newRegistration(
            name: $input->name,
            login: $input->email,
        );

        $userCreated = $this->repository->insert($user, $input->password);

        return new CreateUserOutputDTO(
            id: $userCreated->id,
            name: $userCreated->name,
            email: (string) $userCreated->login,
            createdAt: $userCreated->createdAt,
        );
    }
}
