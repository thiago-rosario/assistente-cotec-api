<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Service;

interface GreetingMessageMatcherServiceInterface
{
    public function matches(string $message): bool;
}
