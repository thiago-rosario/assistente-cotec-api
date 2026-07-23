<?php

declare(strict_types=1);

namespace App\Core\Identity\Enum;

enum IdentityCodeExceptionEnum: int
{
    case UserIdRequired = 1020;
    case UserNameRequired = 1021;
    case UserLoginRequired = 1022;
    case InvalidUserEmail = 1023;
    case UserPasswordRequired = 1024;
    case PasswordConfirmationMismatch = 1025;
    case AuthorizationIdRequired = 1030;
    case WhatsappNumberRequired = 1031;
    case ConversationIdRequired = 1032;
    case InvalidAuthorizationAttemptLimit = 1033;
    case InvalidAuthorizationExpiration = 1034;
    case TemporaryAuthorizationNotFound = 1040;
    case InvalidTemporaryAuthorizationCredentials = 1041;
    case TemporaryAuthorizationExpired = 1042;
    case TemporaryAuthorizationAttemptsExceeded = 1043;
    case TemporaryAuthorizationCancelled = 1044;
    case TemporaryAuthorizationRevoked = 1045;
    case TemporaryAuthorizationPendingCredentials = 1046;
    case TemporaryAuthorizationFinished = 1047;
    case TemporaryAuthorizationContextMismatch = 1048;
    case TemporaryAuthorizationProtectedActionMismatch = 1049;
    case InvalidTemporaryAuthorizationStatusTransition = 1050;
}
