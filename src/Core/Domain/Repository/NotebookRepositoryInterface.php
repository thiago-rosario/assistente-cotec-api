<?php

declare(strict_types=1);

namespace App\Core\Domain\Repository;

use App\Core\Domain\Entity\NotebookEntity;

interface NotebookRepositoryInterface
{
    /**
     * Retorna todos os cadernos disponíveis.
     *
     * @return list<NotebookEntity>
     */
    public function all(): array;

    /**
     * Busca nos cadernos pelo termo informado.
     *
     * @return list<NotebookEntity>
     */
    public function search(string $term): array;

    /**
     * Retorna os cadernos de um município.
     *
     * @return list<NotebookEntity>
     */
    public function findByMunicipality(string $municipality): array;

    /**
     * Localiza um caderno pelo processo relacionado.
     */
    public function findByRelatedProcess(string $process): ?NotebookEntity;

    /**
     * Retorna os cadernos filtrados pelo solicitante.
     *
     * @return list<NotebookEntity>
     */
    public function findByRequester(string $requester): array;

    /**
     * Retorna os cadernos filtrados pela situação do terreno.
     *
     * @return list<NotebookEntity>
     */
    public function findByLandStatus(string $status): array;
}
