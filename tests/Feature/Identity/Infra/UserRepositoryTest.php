<?php

use App\Core\Identity\Domain\Entity\UserEntity;
use App\Core\Identity\Domain\Repository\UserRepositoryInterface;
use App\Core\Identity\Domain\ValueObject\Login;
use App\Core\Identity\Infra\Repository\Gateway\UserGatewayRepository;
use App\Core\Identity\Infra\Repository\Repositories\CreateUserEloquentRepository;
use App\Core\Identity\Infra\Repository\Repositories\DeleteUserEloquentRepository;
use App\Core\Identity\Infra\Repository\Repositories\FindAllUsersEloquentRepository;
use App\Core\Identity\Infra\Repository\Repositories\FindUserByIdEloquentRepository;
use App\Core\Identity\Infra\Repository\Repositories\FindUserByLoginEloquentRepository;
use App\Core\Identity\Infra\Repository\Repositories\UpdateUserEloquentRepository;
use App\Core\Identity\Infra\Repository\Repositories\UpdateUserPasswordEloquentRepository;
use App\Core\Identity\Infra\Service\EloquentCredentialsVerifierService;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(LazilyRefreshDatabase::class);

it('persists and manages users through the eloquent gateway', function (): void {
    $repository = identityInfraUserGatewayRepository();
    $registeredAt = new DateTimeImmutable('2026-07-20 08:30:00');

    $created = $repository->insert(
        user: UserEntity::newRegistration(
            name: 'Thiago Souza',
            login: 'thiago@example.com',
            registeredAt: $registeredAt,
        ),
        plainPassword: 'secret-release',
    );

    $model = User::query()->first();

    expect($repository)->toBeInstanceOf(UserRepositoryInterface::class)
        ->and($created->id)->toBe('1')
        ->and($created->name)->toBe('Thiago Souza')
        ->and((string) $created->login)->toBe('thiago@example.com')
        ->and($model)->toBeInstanceOf(User::class)
        ->and(Hash::check('secret-release', (string) $model->password))->toBeTrue()
        ->and($repository->all())->toHaveCount(1)
        ->and($repository->findById((string) $created->id)?->name)->toBe('Thiago Souza')
        ->and($repository->findByLogin(Login::fromString('thiago@example.com'))?->id)->toBe($created->id);

    $updated = $repository->update(UserEntity::identified(
        id: (string) $created->id,
        name: 'Thiago Atualizado',
        login: 'thiago.atualizado@example.com',
    ));

    expect($updated->name)->toBe('Thiago Atualizado')
        ->and((string) $updated->login)->toBe('thiago.atualizado@example.com')
        ->and($repository->findByLogin(Login::fromString('thiago@example.com')))->toBeNull()
        ->and($repository->findByLogin(Login::fromString('thiago.atualizado@example.com'))?->id)->toBe($created->id)
        ->and($repository->updatePassword((string) $created->id, 'new-secret-release'))->toBeTrue()
        ->and(Hash::check('new-secret-release', (string) User::query()->find($created->id)?->password))->toBeTrue()
        ->and($repository->updatePassword('999', 'missing-user-password'))->toBeFalse()
        ->and($repository->delete((string) $created->id))->toBeTrue()
        ->and($repository->findById((string) $created->id))->toBeNull()
        ->and($repository->delete('999'))->toBeFalse();
});

it('verifies credentials using eloquent users and hashed passwords', function (): void {
    $user = User::factory()->create([
        'email' => 'thiago@example.com',
        'password' => Hash::make('known-secret'),
    ]);

    $verifier = new EloquentCredentialsVerifierService;
    $verified = $verifier->verify(Login::fromString('thiago@example.com'), 'known-secret');

    expect($verified)->toBeInstanceOf(UserEntity::class)
        ->and($verified?->id)->toBe((string) $user->id)
        ->and($verifier->verify(Login::fromString('thiago@example.com'), 'wrong-secret'))->toBeNull()
        ->and($verifier->verify(Login::fromString('missing@example.com'), 'known-secret'))->toBeNull();
});

function identityInfraUserGatewayRepository(): UserGatewayRepository
{
    return new UserGatewayRepository(
        createUserRepository: new CreateUserEloquentRepository,
        findAllUsersRepository: new FindAllUsersEloquentRepository,
        findUserByIdRepository: new FindUserByIdEloquentRepository,
        findUserByLoginRepository: new FindUserByLoginEloquentRepository,
        updateUserRepository: new UpdateUserEloquentRepository,
        updateUserPasswordRepository: new UpdateUserPasswordEloquentRepository,
        deleteUserRepository: new DeleteUserEloquentRepository,
    );
}
