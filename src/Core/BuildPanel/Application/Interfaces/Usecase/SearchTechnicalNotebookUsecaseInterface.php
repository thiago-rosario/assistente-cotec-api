<?php

declare(strict_types=1);

namespace App\Core\BuildPanel\Application\Interfaces\Usecase;

use App\Core\BuildPanel\Application\DTO\SearchTechnicalNotebookInputDTO;
use App\Core\BuildPanel\Application\DTO\SearchTechnicalNotebookOutputDTO;

interface SearchTechnicalNotebookUsecaseInterface
{
    public function __invoke(SearchTechnicalNotebookInputDTO $input): SearchTechnicalNotebookOutputDTO;
}
