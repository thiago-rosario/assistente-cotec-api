<?php

use App\Core\Identity\Application\Interface\ClockInterface;
use App\Core\Identity\Application\Interface\Mapper\TemporaryAuthorizationOutputMapperInterface;
use App\Core\Identity\Application\Interface\Service\TemporaryAuthorizationCredentialsAuthenticatorServiceInterface;
use App\Core\Identity\Application\Interface\Service\TemporaryAuthorizationErrorCodeServiceInterface;
use App\Core\Identity\Application\Interface\Service\TemporaryAuthorizationFinderServiceInterface;
use App\Core\Identity\Application\Interface\Service\TemporaryAuthorizationGuardServiceInterface;
use App\Core\Identity\Application\Interface\Usecase\AuthenticateTemporaryAuthorizationUsecaseInterface;
use App\Core\Identity\Application\Interface\Usecase\CancelTemporaryAuthorizationUsecaseInterface;
use App\Core\Identity\Application\Interface\Usecase\ConsumeTemporaryAuthorizationUsecaseInterface;
use App\Core\Identity\Application\Interface\Usecase\CreateUserUsecaseInterface;
use App\Core\Identity\Application\Interface\Usecase\StartTemporaryAuthorizationUsecaseInterface;
use App\Core\Identity\Application\Interface\Usecase\ValidateTemporaryAuthorizationUsecaseInterface;
use App\Core\Identity\Application\Mapper\TemporaryAuthorizationOutputMapper;
use App\Core\Identity\Application\Service\TemporaryAuthorizationCredentialsAuthenticatorService;
use App\Core\Identity\Application\Service\TemporaryAuthorizationErrorCodeService;
use App\Core\Identity\Application\Service\TemporaryAuthorizationFinderService;
use App\Core\Identity\Application\Service\TemporaryAuthorizationGuardService;
use App\Core\Identity\Application\Usecase\AuthenticateTemporaryAuthorizationUsecase;
use App\Core\Identity\Application\Usecase\CancelTemporaryAuthorizationUsecase;
use App\Core\Identity\Application\Usecase\ConsumeTemporaryAuthorizationUsecase;
use App\Core\Identity\Application\Usecase\CreateUserUsecase;
use App\Core\Identity\Application\Usecase\StartTemporaryAuthorizationUsecase;
use App\Core\Identity\Application\Usecase\ValidateTemporaryAuthorizationUsecase;
use App\Core\Identity\Domain\Repository\TemporaryAuthorizationRepositoryInterface;
use App\Core\Identity\Domain\Repository\UserRepositoryInterface;
use App\Core\Identity\Domain\Service\CredentialsVerifierInterface;
use App\Core\Identity\Infra\Providers\IdentityServiceProvider;
use App\Core\Identity\Infra\Repository\Gateway\TemporaryAuthorizationGatewayRepository;
use App\Core\Identity\Infra\Repository\Gateway\UserGatewayRepository;
use App\Core\Identity\Infra\Service\EloquentCredentialsVerifierService;
use App\Core\Identity\Infra\Service\SystemClock;

it('registers the identity service provider', function (): void {
    $providers = require base_path('bootstrap/providers.php');

    expect($providers)->toContain(IdentityServiceProvider::class);
});

it('resolves identity bindings from the container', function (string $abstract, string $concrete): void {
    expect(app($abstract))->toBeInstanceOf($concrete);
})->with([
    [UserRepositoryInterface::class, UserGatewayRepository::class],
    [TemporaryAuthorizationRepositoryInterface::class, TemporaryAuthorizationGatewayRepository::class],
    [CredentialsVerifierInterface::class, EloquentCredentialsVerifierService::class],
    [ClockInterface::class, SystemClock::class],
    [TemporaryAuthorizationOutputMapperInterface::class, TemporaryAuthorizationOutputMapper::class],
    [TemporaryAuthorizationCredentialsAuthenticatorServiceInterface::class, TemporaryAuthorizationCredentialsAuthenticatorService::class],
    [TemporaryAuthorizationErrorCodeServiceInterface::class, TemporaryAuthorizationErrorCodeService::class],
    [TemporaryAuthorizationFinderServiceInterface::class, TemporaryAuthorizationFinderService::class],
    [TemporaryAuthorizationGuardServiceInterface::class, TemporaryAuthorizationGuardService::class],
    [CreateUserUsecaseInterface::class, CreateUserUsecase::class],
    [StartTemporaryAuthorizationUsecaseInterface::class, StartTemporaryAuthorizationUsecase::class],
    [AuthenticateTemporaryAuthorizationUsecaseInterface::class, AuthenticateTemporaryAuthorizationUsecase::class],
    [ValidateTemporaryAuthorizationUsecaseInterface::class, ValidateTemporaryAuthorizationUsecase::class],
    [ConsumeTemporaryAuthorizationUsecaseInterface::class, ConsumeTemporaryAuthorizationUsecase::class],
    [CancelTemporaryAuthorizationUsecaseInterface::class, CancelTemporaryAuthorizationUsecase::class],
]);
