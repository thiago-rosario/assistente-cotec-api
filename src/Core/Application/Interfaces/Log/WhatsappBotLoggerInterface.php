<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Log;

use Throwable;

interface WhatsappBotLoggerInterface
{
    public function botStarted(array $context = []): void;

    public function messageDetected(array $context = []): void;

    public function messageIgnored(array $context = []): void;

    public function messageProcessingStarted(array $context = []): void;

    public function messageInterpreted(array $context = []): void;

    public function searchFinished(array $context = []): void;

    public function replySent(array $context = []): void;

    public function replySkipped(array $context = []): void;

    public function idleCycles(array $context = []): void;

    public function botError(Throwable $exception, array $context = []): void;

    public function botCritical(Throwable $exception, array $context = []): void;
}
