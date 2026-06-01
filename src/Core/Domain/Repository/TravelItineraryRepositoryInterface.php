<?php

declare(strict_types=1);

namespace App\Core\Domain\Repository;

use App\Core\Domain\Entity\TravelItineraryEntity;

interface TravelItineraryRepositoryInterface
{
    /**
     * Retorna todos os roteiros de viagem disponíveis.
     *
     * @return list<TravelItineraryEntity>
     */
    public function all(): array;

    /**
     * Busca nos roteiros de viagem pelo termo informado.
     *
     * @return list<TravelItineraryEntity>
     */
    public function search(string $term): array;

    /**
     * Retorna os roteiros de viagem de um município.
     *
     * @return list<TravelItineraryEntity>
     */
    public function findByMunicipality(string $municipality): array;

    /**
     * Localiza um roteiro de viagem pelo número do processo.
     */
    public function findByProcess(string $process): ?TravelItineraryEntity;

    /**
     * Retorna os roteiros de viagem ligados a uma força.
     *
     * @return list<TravelItineraryEntity>
     */
    public function findByForce(string $force): array;

    /**
     * Retorna os roteiros de viagem de uma região.
     *
     * @return list<TravelItineraryEntity>
     */
    public function findByRegion(string $region): array;

    /**
     * Retorna os roteiros de viagem filtrados pela situação do terreno.
     *
     * @return list<TravelItineraryEntity>
     */
    public function findByLandStatus(string $status): array;

    /**
     * Retorna os roteiros de viagem filtrados pelo andamento.
     *
     * @return list<TravelItineraryEntity>
     */
    public function findByProgress(string $progress): array;
}
