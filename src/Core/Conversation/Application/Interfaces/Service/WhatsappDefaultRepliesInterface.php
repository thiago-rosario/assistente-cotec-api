<?php

declare(strict_types=1);

namespace App\Core\Conversation\Application\Interfaces\Service;

interface WhatsappDefaultRepliesInterface
{
    public function noRecords(): string;

    public function greeting(): string;

    public function buildPanelConsultation(): string;

    public function unknownIntent(): string;

    public function unsupportedMessageContent(): string;

    public function rateLimited(): string;

    public function dataSourceUnavailable(): string;

    public function error(): string;
}
