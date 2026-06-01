<?php

declare(strict_types=1);

namespace App\Core\Domain\Repository;

use App\Core\Domain\Entity\LandSurveyEntity;

interface LandSurveyRepositoryInterface
{
    /**
     * Retorna todos os levantamentos de terreno disponíveis.
     *
     * @return list<LandSurveyEntity>
     */
    public function all(): array;

    /**
     * Busca nos levantamentos de terreno pelo termo informado.
     *
     * @return list<LandSurveyEntity>
     */
    public function search(string $term): array;

    /**
     * Retorna os levantamentos de terreno de um município.
     *
     * @return list<LandSurveyEntity>
     */
    public function findByMunicipality(string $municipality): array;

    /**
     * Localiza um levantamento de terreno pelo número do processo.
     */
    public function findByProcess(string $process): ?LandSurveyEntity;

    /**
     * Retorna os levantamentos de terreno ligados a uma força.
     *
     * @return list<LandSurveyEntity>
     */
    public function findByForce(string $force): array;

    /**
     * Retorna os levantamentos de terreno de uma região.
     *
     * @return list<LandSurveyEntity>
     */
    public function findByRegion(string $region): array;

    /**
     * Retorna os levantamentos de terreno filtrados pela situação do terreno.
     *
     * @return list<LandSurveyEntity>
     */
    public function findByLandStatus(string $status): array;

    /**
     * Retorna os levantamentos de terreno filtrados pelo andamento.
     *
     * @return list<LandSurveyEntity>
     */
    public function findByProgress(string $progress): array;
}
