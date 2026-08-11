<?php

declare(strict_types=1);

namespace App\Core\Application\Handler;

use App\BuildPanel\Application\Interfaces\Service\MunicipalityExtractorServiceInterface;
use App\Core\Application\Interfaces\Handler\WhatsappConversationFlowHandlerInterface;
use App\Core\Application\Interfaces\Service\GreetingMessageMatcherServiceInterface;
use App\Core\Application\Interfaces\Service\MessageIntentResolverInterface;
use App\Core\Application\Interfaces\Service\WhatsappMainMenuServiceInterface;
use App\Core\Domain\Entity\MessageEntity;
use Illuminate\Support\Str;

class MainMenuRequestWhatsappConversationFlowHandler implements WhatsappConversationFlowHandlerInterface
{
    public function __construct(
        private readonly MessageIntentResolverInterface $intentResolver,
        private readonly GreetingMessageMatcherServiceInterface $greetingMatcher,
        private readonly MunicipalityExtractorServiceInterface $municipalityExtractor,
        private readonly WhatsappMainMenuServiceInterface $mainMenu,
    ) {}

    public function supports(MessageEntity $message): bool
    {
        return $this->standaloneMunicipality($message) !== null
            || $this->intentResolver->isMainMenuRequest($message)
            || $this->greetingMatcher->matches($message->content());
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function handle(MessageEntity $message): array
    {
        $municipality = $this->standaloneMunicipality($message);

        if ($municipality !== null) {
            return $this->mainMenu->showMunicipalityChoice($message, $municipality);
        }

        return $this->mainMenu->show($message);
    }

    private function standaloneMunicipality(MessageEntity $message): ?string
    {
        $municipality = $this->municipalityExtractor->extract($message->content());

        if ($municipality === null) {
            return null;
        }

        $normalizedMunicipality = Str::of($municipality)
            ->lower()
            ->ascii()
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();

        return $message->normalizedContent() === $normalizedMunicipality ? $municipality : null;
    }
}
