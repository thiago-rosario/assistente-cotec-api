<?php

declare(strict_types=1);

namespace App\Core\Identity\Infra\Providers;

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
use App\Core\Identity\Infra\Repository\Gateway\TemporaryAuthorizationGatewayRepository;
use App\Core\Identity\Infra\Repository\Gateway\UserGatewayRepository;
use App\Core\Identity\Infra\Service\EloquentCredentialsVerifierService;
use App\Core\Identity\Infra\Service\SystemClock;
use Illuminate\Support\ServiceProvider;

class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserGatewayRepository::class);
        $this->app->bind(TemporaryAuthorizationRepositoryInterface::class, TemporaryAuthorizationGatewayRepository::class);
        $this->app->bind(CredentialsVerifierInterface::class, EloquentCredentialsVerifierService::class);
        $this->app->bind(ClockInterface::class, SystemClock::class);
        $this->app->bind(TemporaryAuthorizationOutputMapperInterface::class, TemporaryAuthorizationOutputMapper::class);
        $this->app->bind(TemporaryAuthorizationCredentialsAuthenticatorServiceInterface::class, TemporaryAuthorizationCredentialsAuthenticatorService::class);
        $this->app->bind(TemporaryAuthorizationErrorCodeServiceInterface::class, TemporaryAuthorizationErrorCodeService::class);
        $this->app->bind(TemporaryAuthorizationFinderServiceInterface::class, TemporaryAuthorizationFinderService::class);
        $this->app->bind(TemporaryAuthorizationGuardServiceInterface::class, TemporaryAuthorizationGuardService::class);
        $this->app->bind(CreateUserUsecaseInterface::class, CreateUserUsecase::class);
        $this->app->bind(StartTemporaryAuthorizationUsecaseInterface::class, StartTemporaryAuthorizationUsecase::class);
        $this->app->bind(AuthenticateTemporaryAuthorizationUsecaseInterface::class, AuthenticateTemporaryAuthorizationUsecase::class);
        $this->app->bind(ValidateTemporaryAuthorizationUsecaseInterface::class, ValidateTemporaryAuthorizationUsecase::class);
        $this->app->bind(ConsumeTemporaryAuthorizationUsecaseInterface::class, ConsumeTemporaryAuthorizationUsecase::class);
        $this->app->bind(CancelTemporaryAuthorizationUsecaseInterface::class, CancelTemporaryAuthorizationUsecase::class);
    }

    public function boot(): void {}
}
