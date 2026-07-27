<?php

declare(strict_types=1);

namespace App\Core\Application\Support;

final class WhatsappLogContext
{
    public static function maskPhone(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return '***';
        }

        if (mb_strlen($digits) <= 4) {
            return str_repeat('*', mb_strlen($digits));
        }

        $prefix = mb_substr($digits, 0, min(4, mb_strlen($digits) - 2));
        $suffix = mb_substr($digits, -2);

        return $prefix.str_repeat('*', max(2, mb_strlen($digits) - mb_strlen($prefix) - 2)).$suffix;
    }

    /**
     * @return array{external_id: string|null, phone: string|null, source: string|null}
     */
    public static function message(?string $externalId, ?string $phone, ?string $source): array
    {
        return [
            'external_id' => $externalId,
            'phone' => self::maskPhone($phone),
            'source' => $source,
        ];
    }
}
