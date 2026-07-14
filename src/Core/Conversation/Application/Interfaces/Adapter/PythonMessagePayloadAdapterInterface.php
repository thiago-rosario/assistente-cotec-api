<?php

declare(strict_types=1);

namespace App\Core\Conversation\Application\Interfaces\Adapter;

use App\Core\Conversation\Application\DTO\ReceivedMessageInputDTO;

interface PythonMessagePayloadAdapterInterface
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function fromArray(array $payload): ReceivedMessageInputDTO;

    /**
     * @return array<string, mixed>
     */
    public function toArray(ReceivedMessageInputDTO $dto): array;

    /**
     * @return list<ReceivedMessageInputDTO>
     */
    public function fromPythonOutput(string $output): array;
}
