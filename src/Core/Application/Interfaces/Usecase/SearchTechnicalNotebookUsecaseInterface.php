<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Usecase;

use App\Core\Application\DTO\SearchTechnicalNotebookInputDTO;
use App\Core\Application\DTO\SearchTechnicalNotebookOutputDTO;

interface SearchTechnicalNotebookUsecaseInterface
{
    public function __invoke(SearchTechnicalNotebookInputDTO $input): SearchTechnicalNotebookOutputDTO;
}
