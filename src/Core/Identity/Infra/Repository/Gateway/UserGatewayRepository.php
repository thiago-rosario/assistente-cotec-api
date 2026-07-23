<?php

declare(strict_types=1);

namespace App\Core\Identity\Infra\Repository\Gateway;

use App\Core\Identity\Domain\Entity\UserEntity;
use App\Core\Identity\Domain\Repository\UserRepositoryInterface;
use App\Core\Identity\Domain\ValueObject\Login;
use App\Core\Identity\Infra\Repository\Repositories\CreateUserEloquentRepository;
use App\Core\Identity\Infra\Repository\Repositories\DeleteUserEloquentRepository;
use App\Core\Identity\Infra\Repository\Repositories\FindAllUsersEloquentRepository;
use App\Core\Identity\Infra\Repository\Repositories\FindUserByIdEloquentRepository;
use App\Core\Identity\Infra\Repository\Repositories\FindUserByLoginEloquentRepository;
use App\Core\Identity\Infra\Repository\Repositories\UpdateUserEloquentRepository;
use App\Core\Identity\Infra\Repository\Repositories\UpdateUserPasswordEloquentRepository;

final readonly class UserGatewayRepository implements UserRepositoryInterface
{
    public function __construct(
        private CreateUserEloquentRepository $createUserRepository,
        private FindAllUsersEloquentRepository $findAllUsersRepository,
        private FindUserByIdEloquentRepository $findUserByIdRepository,
        private FindUserByLoginEloquentRepository $findUserByLoginRepository,
        private UpdateUserEloquentRepository $updateUserRepository,
        private UpdateUserPasswordEloquentRepository $updateUserPasswordRepository,
        private DeleteUserEloquentRepository $deleteUserRepository,
    ) {}

    public function insert(UserEntity $user, string $plainPassword): UserEntity
    {
        return $this->createUserRepository->insert($user, $plainPassword);
    }

    public function all(): array
    {
        return $this->findAllUsersRepository->all();
    }

    public function findById(string $id): ?UserEntity
    {
        return $this->findUserByIdRepository->findById($id);
    }

    public function findByLogin(Login $login): ?UserEntity
    {
        return $this->findUserByLoginRepository->findByLogin($login);
    }

    public function update(UserEntity $user): UserEntity
    {
        return $this->updateUserRepository->update($user);
    }

    public function updatePassword(string $id, string $plainPassword): bool
    {
        return $this->updateUserPasswordRepository->updatePassword($id, $plainPassword);
    }

    public function delete(string $id): bool
    {
        return $this->deleteUserRepository->delete($id);
    }
}
