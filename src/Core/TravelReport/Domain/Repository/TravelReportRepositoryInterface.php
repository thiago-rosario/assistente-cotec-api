<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Domain\Repository;

use App\Core\TravelReport\Domain\Entity\TravelReportEntity;

interface TravelReportRepositoryInterface
{
    /**
     * Persiste um novo relatório de viagem submetido.
     */
    public function insert(TravelReportEntity $travelReport): TravelReportEntity;

    /**
     * Localiza um relatório de viagem pelo identificador.
     */
    public function findById(int $id): ?TravelReportEntity;

    /**
     * Localiza um relatório de viagem pelo processo SEI.
     */
    public function findBySeiProcess(string $seiProcess): ?TravelReportEntity;

    /**
     * Retorna os relatórios de viagem submetidos por um usuário.
     *
     * @return list<TravelReportEntity>
     */
    public function findBySubmittedByUserId(string $submittedByUserId): array;

    /**
     * Retorna os relatórios de viagem de um município.
     *
     * @return list<TravelReportEntity>
     */
    public function findByMunicipalityId(int $municipalityId): array;
}
