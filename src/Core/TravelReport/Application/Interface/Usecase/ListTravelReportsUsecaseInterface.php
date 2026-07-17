<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Application\Interface\Usecase;

use App\Core\TravelReport\Application\DTO\ListTravelReportsInputDTO;
use App\Core\TravelReport\Application\DTO\ListTravelReportsOutputDTO;

interface ListTravelReportsUsecaseInterface
{
    public function __invoke(ListTravelReportsInputDTO $dto): ListTravelReportsOutputDTO;
}
