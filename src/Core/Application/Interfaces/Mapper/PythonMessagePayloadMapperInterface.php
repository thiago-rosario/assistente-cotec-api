<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Mapper;

interface PythonMessagePayloadMapperInterface
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array{
     *     message: string,
     *     customer_contact: string|null,
     *     sender_name: string|null,
     *     received_at: string|null,
     *     source: string,
     *     external_id: string|null,
     *     metadata: array<string, mixed>
     * }
     */
    public function map(array $payload): array;
}
