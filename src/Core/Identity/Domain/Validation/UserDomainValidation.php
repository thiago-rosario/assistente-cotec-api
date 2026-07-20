<?php

declare(strict_types=1);

namespace App\Core\Identity\Domain\Validation;

use App\Core\Identity\Exception\InvalidUserEmailException;
use App\Core\Identity\Exception\UserIdRequiredException;
use App\Core\Identity\Exception\UserLoginRequiredException;
use App\Core\Identity\Exception\UserNameRequiredException;
use App\Core\Identity\Exception\UserPasswordRequiredException;

final class UserDomainValidation
{
    public static function validateId(string $id): void
    {
        if (trim($id) === '') {
            throw new UserIdRequiredException;
        }
    }

    public static function validateName(string $name): void
    {
        if (trim($name) === '') {
            throw new UserNameRequiredException;
        }
    }

    public static function validateLogin(string $login): void
    {
        if (trim($login) === '') {
            throw new UserLoginRequiredException;
        }
    }

    public static function validateEmail(string $email): void
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidUserEmailException;
        }
    }

    public static function validatePassword(string $password): void
    {
        if (trim($password) === '') {
            throw new UserPasswordRequiredException;
        }
    }
}
