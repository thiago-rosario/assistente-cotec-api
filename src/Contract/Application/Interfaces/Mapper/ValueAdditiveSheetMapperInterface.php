<?php

declare(strict_types=1);

namespace App\Contract\Application\Interfaces\Mapper;

use App\Contract\Domain\Entity\ValueAdditiveEntity;

interface ValueAdditiveSheetMapperInterface
{
    public function map(array $row): ValueAdditiveEntity;
}
