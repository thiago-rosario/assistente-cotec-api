<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Mapper;

interface WhatsappWebhookPayloadMapperInterface
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array{
     *     message: string|null,
     *     customer_contact: string|null,
     *     sender_name: string|null,
     *     received_at: string|null,
     *     source: string,
     *     external_id: string|null,
     *     document: array{
     *         original_file_name: string,
     *         mime_type: string,
     *         size_bytes: int,
     *         caption: string|null,
     *         content_base64: string|null,
     *         metadata: array<string, mixed>
     *     }|null,
     *     caption: string|null,
     *     metadata: array<string, mixed>
     * }
     */
    public function map(array $payload): array;
}
