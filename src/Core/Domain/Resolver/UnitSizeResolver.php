<?php

declare(strict_types=1);

namespace App\Core\Domain\Resolver;

final readonly class UnitSizeResolver
{
    private const SIZES = [
        '1B PM' => [
            'code' => '1B PM',
            'classification' => 'Pequeno',
            'equivalent_code' => '1B PC',
            'equivalent_classification' => 'Pequeno',
            'standard_size' => '38 x22',
            'standard_area' => '850m²',
            'type' => 'Individual',
            'reduced_size' => '32 x 12',
            'reduced_area' => '385m²',
        ],

        '1A PM' => [
            'code' => '1A PM',
            'classification' => 'Médio',
            'equivalent_code' => '1A PC',
            'equivalent_classification' => 'Médio',
            'standard_size' => '40 x 32',
            'standard_area' => '1.300m²',
            'type' => 'Individual',
            'reduced_size' => '32 x 22',
            'reduced_area' => '705 m²',
        ],

        '1PM' => [
            'code' => '1PM',
            'classification' => 'Grande',
            'equivalent_code' => '1 PC',
            'equivalent_classification' => 'Grande',
            'standard_size' => '51 x 35',
            'standard_area' => '1.820m²',
            'type' => 'Individual',
            'reduced_size' => '43 x 24',
            'reduced_area' => '1.032 m²',
        ],

        'Conjugada 1A + 1B Média/Pequena' => [
            'code' => 'Conjugada 1A + 1B Média/Pequena',
            'classification' => null,
            'equivalent_code' => null,
            'equivalent_classification' => null,
            'standard_size' => '45 x 42',
            'standard_area' => '2.000m²',
            'type' => 'Conjugada',
            'reduced_size' => null,
            'reduced_area' => null,
        ],

        'Conjugada pequena 1B + 1B' => [
            'code' => 'Conjugada pequena 1B + 1B',
            'classification' => null,
            'equivalent_code' => null,
            'equivalent_classification' => null,
            'standard_size' => '40 x 40',
            'standard_area' => '1600m²',
            'type' => 'Conjugada',
            'reduced_size' => null,
            'reduced_area' => null,
        ],

        'CIPM M' => [
            'code' => 'CIPM M',
            'classification' => null,
            'equivalent_code' => null,
            'equivalent_classification' => null,
            'standard_size' => '22 x 38',
            'standard_area' => '850m²',
            'type' => 'Individual',
            'reduced_size' => null,
            'reduced_area' => null,
        ],

        'Central de Custódia DPT' => [
            'code' => 'Central de Custódia DPT',
            'classification' => null,
            'equivalent_code' => null,
            'equivalent_classification' => null,
            'standard_size' => '23,8 x 8,6',
            'standard_area' => '205 m²',
            'type' => 'Individual',
            'reduced_size' => null,
            'reduced_area' => null,
        ],
    ];

    /**
     * @return array<string, array<string, string|null>>
     */
    public function all(): array
    {
        return self::SIZES;
    }

    /**
     * Busca exata.
     *
     * Exemplo:
     * 1B PM
     * 1A PM
     * CIPM M
     */
    public function findByCode(string $code): ?array
    {
        $normalizedCode = $this->normalize($code);

        foreach (self::SIZES as $size) {
            if ($this->normalize((string) $size['code']) === $normalizedCode) {
                return $size;
            }

            if (
                isset($size['equivalent_code']) &&
                $size['equivalent_code'] !== null &&
                $this->normalize((string) $size['equivalent_code']) === $normalizedCode
            ) {
                return $size;
            }
        }

        return null;
    }

    /**
     * Busca parcial.
     *
     * Exemplo:
     * 1B
     * PM
     * Conjugada
     */
    public function search(string $term): array
    {
        $normalizedTerm = $this->normalize($term);

        return array_values(array_filter(
            self::SIZES,
            function (array $size) use ($normalizedTerm): bool {
                $code = $this->normalize((string) $size['code']);
                $equivalentCode = $this->normalize((string) ($size['equivalent_code'] ?? ''));

                return str_contains($code, $normalizedTerm)
                    || str_contains($equivalentCode, $normalizedTerm);
            }
        ));
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim($value));
    }
}
