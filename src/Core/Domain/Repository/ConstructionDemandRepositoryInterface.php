<?php

declare(strict_types=1);

namespace App\Core\Domain\Repository;

use App\Core\Domain\Entity\ConstructionDemandEntity;

interface ConstructionDemandRepositoryInterface
{
    /**
     * Retorna todas as demandas de construção disponíveis.
     *
     * @return list<ConstructionDemandEntity>
     */
    public function all(): array;

    /**
     * Busca nas demandas de construção pelo termo informado.
     *
     * @return list<ConstructionDemandEntity>
     */
    public function search(string $term): array;

    /**
     * Retorna as demandas de construção de um município.
     *
     * @return list<ConstructionDemandEntity>
     */
    public function findByMunicipality(string $municipality): array;

    /**
     * Localiza uma demanda de construção pelo número do processo.
     */
    public function findByProcess(string $process): ?ConstructionDemandEntity;

    /**
     * Retorna as demandas de construção ligadas a uma força.
     *
     * @return list<ConstructionDemandEntity>
     */
    public function findByForce(string $force): array;

    /**
     * Retorna as demandas de construção de uma região.
     *
     * @return list<ConstructionDemandEntity>
     */
    public function findByRegion(string $region): array;

    /**
     * Retorna as demandas de construção filtradas pela situação do terreno.
     *
     * @return list<ConstructionDemandEntity>
     */
    public function findByLandStatus(string $status): array;

    /**
     * Retorna as demandas de construção filtradas pelo andamento.
     *
     * @return list<ConstructionDemandEntity>
     */
    public function findByProgress(string $progress): array;
}
