<?php

declare(strict_types=1);

namespace App\Core\Domain\Repository;

use App\Core\Domain\Entity\TechnicalNotebookEntity;

interface TechnicalNotebookRepositoryInterface
{
    /**
     * Retorna todos os dados do caderno técnico disponíveis.
     *
     * @return list<TechnicalNotebookEntity>
     */
    public function all(): array;

    /**
     * Busca  no caderno técnico pelo termo informado.
     *
     * @return list<TechnicalNotebookEntity>
     */
    public function search(string $term): array;

    /**
     * Retorna os dados do caderno técnico de um município.
     *
     * @return list<TechnicalNotebookEntity>
     */
    public function findByMunicipality(string $municipality): array;

    /**
     * Localiza um caderno técnico pelo número do processo.
     */
    public function findByProcess(string $process): ?TechnicalNotebookEntity;

    /**
     * Retorna os dados do caderno técnico ligados a uma força.
     *
     * @return list<TechnicalNotebookEntity>
     */
    public function findByForce(string $force): array;

    /**
     * Retorna os dados do caderno técnico filtrados pela situação da obra.
     *
     * @return list<TechnicalNotebookEntity>
     */
    public function findByBuildStatus(string $status): array;
}
