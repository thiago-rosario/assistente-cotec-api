<?php

use App\Core\Identity\Domain\Entity\UserEntity;
use App\Core\Identity\Domain\Repository\UserRepositoryInterface;
use App\Core\Identity\Domain\ValueObject\Login;

it('defines the user repository contract for user lifecycle operations', function (): void {
    $interface = new ReflectionClass(UserRepositoryInterface::class);

    $assertMethodSignature = function (
        string $methodName,
        array $parameters,
        string $returnType,
        bool $allowsNullReturn = false,
    ) use ($interface): void {
        $method = $interface->getMethod($methodName);
        $methodReturnType = $method->getReturnType();

        expect($methodReturnType)->toBeInstanceOf(ReflectionNamedType::class)
            ->and($methodReturnType->getName())->toBe($returnType)
            ->and($methodReturnType->allowsNull())->toBe($allowsNullReturn)
            ->and($method->getParameters())->toHaveCount(count($parameters));

        foreach ($parameters as $index => $expectedParameter) {
            $parameter = $method->getParameters()[$index];
            $parameterType = $parameter->getType();

            expect($parameter->getName())->toBe($expectedParameter['name'])
                ->and($parameterType)->toBeInstanceOf(ReflectionNamedType::class)
                ->and($parameterType->getName())->toBe($expectedParameter['type']);
        }
    };

    expect($interface->isInterface())->toBeTrue();

    $assertMethodSignature('insert', [
        ['name' => 'user', 'type' => UserEntity::class],
        ['name' => 'plainPassword', 'type' => 'string'],
    ], UserEntity::class);

    $assertMethodSignature('all', [], 'array');

    $assertMethodSignature('findById', [
        ['name' => 'id', 'type' => 'string'],
    ], UserEntity::class, allowsNullReturn: true);

    $assertMethodSignature('findByLogin', [
        ['name' => 'login', 'type' => Login::class],
    ], UserEntity::class, allowsNullReturn: true);

    $assertMethodSignature('update', [
        ['name' => 'user', 'type' => UserEntity::class],
    ], UserEntity::class);

    $assertMethodSignature('updatePassword', [
        ['name' => 'id', 'type' => 'string'],
        ['name' => 'plainPassword', 'type' => 'string'],
    ], 'bool');

    $assertMethodSignature('delete', [
        ['name' => 'id', 'type' => 'string'],
    ], 'bool');
});
