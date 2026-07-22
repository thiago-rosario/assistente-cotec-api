<?php

use App\Core\Identity\Domain\Entity\UserEntity;
use App\Core\Identity\Domain\Validation\UserDomainValidation;
use App\Core\Identity\Domain\ValueObject\Login;
use App\Core\Identity\Enum\IdentityCodeExceptionEnum;
use App\Core\Identity\Exception\InvalidUserEmailException;
use App\Core\Identity\Exception\UserIdRequiredException;
use App\Core\Identity\Exception\UserLoginRequiredException;
use App\Core\Identity\Exception\UserNameRequiredException;
use App\Core\Identity\Exception\UserPasswordRequiredException;

it('represents an identified user without exposing passwords', function (): void {
    $createdAt = new DateTimeImmutable('2026-07-20 08:30:00');

    $entity = UserEntity::identified(
        id: '10',
        name: 'Thiago Souza',
        login: 'thiago@example.com',
        createdAt: $createdAt,
    );

    expect($entity->id)->toBe('10')
        ->and($entity->name)->toBe('Thiago Souza')
        ->and((string) $entity->login)->toBe('thiago@example.com')
        ->and(isset($entity->password))->toBeFalse()
        ->and($entity->createdAt)->toBe($createdAt)
        ->and($entity->updatedAt)->toBe($createdAt)
        ->and($entity->toAuthorizationArray())->toBe([
            'id' => '10',
            'name' => 'Thiago Souza',
            'login' => 'thiago@example.com',
        ]);
});

it('represents a new user registration before persistence', function (): void {
    $registeredAt = new DateTimeImmutable('2026-07-20 08:30:00');

    $entity = UserEntity::newRegistration(
        name: 'Thiago Souza',
        login: Login::fromString('thiago@example.com'),
        registeredAt: $registeredAt,
    );

    expect($entity->id)->toBeNull()
        ->and($entity->name)->toBe('Thiago Souza')
        ->and((string) $entity->login)->toBe('thiago@example.com')
        ->and($entity->createdAt)->toBe($registeredAt)
        ->and($entity->updatedAt)->toBe($registeredAt)
        ->and($entity->toPersistenceArray())->toBe([
            'name' => 'Thiago Souza',
            'email' => 'thiago@example.com',
            'created_at' => $registeredAt,
            'updated_at' => $registeredAt,
        ]);
});

it('normalizes login value objects', function (): void {
    $login = Login::fromString(' thiago@example.com ');

    expect($login->value)->toBe('thiago@example.com')
        ->and((string) $login)->toBe('thiago@example.com')
        ->and($login->equals(Login::fromString('thiago@example.com')))->toBeTrue();
});

it('checks whether the identified user matches an id', function (): void {
    $entity = UserEntity::identified(
        id: '10',
        name: 'Thiago Souza',
        login: Login::fromString('thiago@example.com'),
    );

    expect($entity->isSameUser('10'))->toBeTrue()
        ->and($entity->isSameUser('11'))->toBeFalse();
});

it('rejects required user data with domain exceptions', function (
    array $attributes,
    string $exception,
    string $message,
): void {
    try {
        new UserEntity(...array_merge(validIdentityUserAttributes(), $attributes));
    } catch (Throwable $throwable) {
        expect($throwable)->toBeInstanceOf($exception)
            ->and($throwable->getMessage())->toBe($message);

        return;
    }

    $this->fail("Expected {$exception} to be thrown.");
})->with([
    'id' => [
        ['id' => ' '],
        UserIdRequiredException::class,
        'O identificador do usuário é obrigatório.',
    ],
    'name' => [
        ['name' => ' '],
        UserNameRequiredException::class,
        'O nome do usuário é obrigatório.',
    ],
    'login' => [
        ['login' => ' '],
        UserLoginRequiredException::class,
        'O login do usuário é obrigatório.',
    ],
]);

it('validates credential input without storing the plain password', function (): void {
    expect(fn () => UserDomainValidation::validatePassword(' '))
        ->toThrow(UserPasswordRequiredException::class, 'A senha do usuário é obrigatória.');
});

it('keeps email validation available for email based login repositories', function (): void {
    expect(fn () => UserDomainValidation::validateEmail('invalid-email'))
        ->toThrow(InvalidUserEmailException::class, 'O e-mail do usuário é inválido.');
});

it('requires an identified user for authorization payloads', function (): void {
    $entity = UserEntity::newRegistration(
        name: 'Thiago Souza',
        login: 'thiago@example.com',
    );

    expect(fn () => $entity->toAuthorizationArray())
        ->toThrow(UserIdRequiredException::class, 'O identificador do usuário é obrigatório.');
});

it('rejects access to unknown magic attributes', function (): void {
    $entity = new UserEntity(...validIdentityUserAttributes());

    $entity->unknownAttribute;
})->throws(LogicException::class);

it('defines identity exception defaults', function (
    RuntimeException $exception,
    int $code,
    string $message,
): void {
    expect($exception)->toBeInstanceOf(RuntimeException::class)
        ->and($exception->getCode())->toBe($code)
        ->and($exception->getMessage())->toBe($message);
})->with([
    'user id required' => [
        new UserIdRequiredException,
        IdentityCodeExceptionEnum::UserIdRequired->value,
        'O identificador do usuário é obrigatório.',
    ],
    'user name required' => [
        new UserNameRequiredException,
        IdentityCodeExceptionEnum::UserNameRequired->value,
        'O nome do usuário é obrigatório.',
    ],
    'user login required' => [
        new UserLoginRequiredException,
        IdentityCodeExceptionEnum::UserLoginRequired->value,
        'O login do usuário é obrigatório.',
    ],
    'user password required' => [
        new UserPasswordRequiredException,
        IdentityCodeExceptionEnum::UserPasswordRequired->value,
        'A senha do usuário é obrigatória.',
    ],
]);

/**
 * @return array<string, mixed>
 */
function validIdentityUserAttributes(): array
{
    return [
        'id' => '10',
        'name' => 'Thiago Souza',
        'login' => 'thiago@example.com',
    ];
}
