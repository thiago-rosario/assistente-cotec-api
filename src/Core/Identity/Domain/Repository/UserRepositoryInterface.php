<?php

declare(strict_types=1);

namespace App\Core\Identity\Domain\Repository;

use App\Core\Identity\Domain\Entity\UserEntity;
use App\Core\Identity\Domain\ValueObject\Login;

interface UserRepositoryInterface
{
    /**
     * Persiste um novo usuário.
     */
    public function insert(UserEntity $user): UserEntity;

    /**
     * Retorna todos os usuários.
     *
     * @return list<UserEntity>
     */
    public function all(): array;

    /**
     * Localiza um usuário pelo identificador.
     */
    public function findById(string $id): ?UserEntity;

    /**
     * Localiza um usuário pelo login.
     */
    public function findByLogin(Login $login): ?UserEntity;

    /**
     * Atualiza os dados cadastrais de um usuário.
     */
    public function update(UserEntity $user): UserEntity;

    /**
     * Atualiza a senha de um usuário sem expor credenciais na entidade.
     */
    public function updatePassword(string $id, string $plainPassword): bool;

    /**
     * Remove um usuário pelo identificador.
     */
    public function delete(string $id): bool;
}
