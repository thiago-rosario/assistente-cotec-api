<?php

use App\Core\TravelReport\Domain\Entity\TravelReportEntity;
use App\Core\TravelReport\Domain\Repository\TravelReportRepositoryInterface;

it('defines the travel report repository contract for entity persistence and queries', function () {
    $interface = new ReflectionClass(TravelReportRepositoryInterface::class);

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
        ['name' => 'travelReport', 'type' => TravelReportEntity::class],
    ], TravelReportEntity::class);

    $assertMethodSignature('findById', [
        ['name' => 'id', 'type' => 'int'],
    ], TravelReportEntity::class, allowsNullReturn: true);

    $assertMethodSignature('findBySeiProcess', [
        ['name' => 'seiProcess', 'type' => 'string'],
    ], TravelReportEntity::class, allowsNullReturn: true);

    $assertMethodSignature('findBySubmittedByUserId', [
        ['name' => 'submittedByUserId', 'type' => 'string'],
    ], 'array');

    $assertMethodSignature('findByMunicipalityId', [
        ['name' => 'municipalityId', 'type' => 'int'],
    ], 'array');
});
