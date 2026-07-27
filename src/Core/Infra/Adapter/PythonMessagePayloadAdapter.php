<?php

declare(strict_types=1);

namespace App\Core\Infra\Adapter;

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Interfaces\Adapter\PythonMessagePayloadAdapterInterface;
use App\Core\Application\Interfaces\Mapper\PythonMessagePayloadMapperInterface;
use App\Core\Application\Interfaces\Parser\PythonMessageOutputParserInterface;
use App\Core\Domain\Resolver\PhoneNormalizerResolver;

class PythonMessagePayloadAdapter extends WhatsappWebhookPayloadAdapter implements PythonMessagePayloadAdapterInterface
{
    public function __construct(
        PythonMessagePayloadMapperInterface $mapper,
        private readonly PythonMessageOutputParserInterface $parser,
        PhoneNormalizerResolver $resolver,
    ) {
        parent::__construct($mapper, $resolver);
    }

    /**
     * @return list<ReceivedMessageInputDTO>
     */
    public function fromPythonOutput(string $output): array
    {
        return array_map(
            fn (array $payload): ReceivedMessageInputDTO => $this->fromArray($payload),
            $this->parser->parse($output),
        );
    }
}
