<?php

declare(strict_types=1);

namespace App\Core\Application\Usecase;

use App\BuildPanel\Application\Interfaces\Rule\SeiProcessWhatsappMessageInterpretationRuleInterface;
use App\BuildPanel\Application\Interfaces\Service\MunicipalityExtractorServiceInterface;
use App\Contract\Application\Interfaces\Service\ContractWhatsappMessageServiceInterface;
use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\DTO\WhatsappConversationStateDTO;
use App\Core\Application\Interfaces\Repository\WhatsappConversationStateStoreInterface;
use App\Core\Application\Interfaces\Service\BuildPanelWhatsappMessageServiceInterface;
use App\Core\Application\Interfaces\Service\CoreWhatsappResponseFormatterInterface;
use App\Core\Application\Interfaces\Service\GreetingMessageMatcherServiceInterface;
use App\Core\Application\Interfaces\Service\WhatsappMessageResponseFormatterInterface;
use App\Core\Application\Interfaces\Usecase\ProcessWhatsappMessageUsecaseInterface;
use Google\Service\Exception as GoogleServiceException;
use GuzzleHttp\Exception\ConnectException;
use OpenAI\Exceptions\RateLimitException;
use Throwable;

class ProcessWhatsappMessageUsecase implements ProcessWhatsappMessageUsecaseInterface
{
    private const string MunicipalityDisambiguationRoute = 'municipality_disambiguation';

    private const string BuildPanelRoute = 'build_panel';

    private const string ContractMenuRoute = 'contract_menu';

    private const string ContractSearchRoute = 'contract_search';

    public function __construct(
        private readonly GreetingMessageMatcherServiceInterface $greetingMatcher,
        private readonly BuildPanelWhatsappMessageServiceInterface $buildPanel,
        private readonly WhatsappMessageResponseFormatterInterface $responseFormatter,
        private readonly ?CoreWhatsappResponseFormatterInterface $coreResponseFormatter = null,
        private readonly ?WhatsappConversationStateStoreInterface $conversationState = null,
        private readonly ?ContractWhatsappMessageServiceInterface $contract = null,
        private readonly ?MunicipalityExtractorServiceInterface $municipalityExtractor = null,
        private readonly ?SeiProcessWhatsappMessageInterpretationRuleInterface $seiProcessRule = null,
    ) {}

    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    public function __invoke(ReceivedMessageInputDTO $input): array
    {
        try {
            if (trim($input->message) === '') {
                return $this->coreResponseFormatter?->unsupportedMessageContent()
                    ?? $this->responseFormatter->unsupportedMessageContent();
            }

            if ($this->greetingMatcher->matches($input->message)) {
                if (! $this->hasConversationIntegration()) {
                    return $this->responseFormatter->greeting();
                }

                $this->conversationState?->forget($input->phone);

                return $this->coreResponseFormatter->mainMenu();
            }

            if (! $this->hasConversationIntegration()) {
                return $this->buildPanel->process($input->message);
            }

            $state = $this->conversationState->get($input->phone);

            if ($state !== null) {
                if ($state->route !== self::BuildPanelRoute
                    && $this->seiProcessRule->__invoke($input->message) !== null) {
                    return $this->mainMenu($input->phone);
                }

                return $this->processState($input, $state);
            }

            if ($this->isOption($input->message, '0')) {
                return $this->mainMenu($input->phone);
            }

            if ($this->isOption($input->message, '1')) {
                $this->conversationState->put($input->phone, new WhatsappConversationStateDTO(
                    route: self::BuildPanelRoute,
                ));

                return $this->responseFormatter->greeting();
            }

            if ($this->isOption($input->message, '2')) {
                $this->conversationState->put($input->phone, new WhatsappConversationStateDTO(
                    route: self::ContractMenuRoute,
                ));

                return $this->contract->menu();
            }

            if (ctype_digit(trim($input->message))) {
                return $this->coreResponseFormatter->invalidMainMenuOption();
            }

            if ($this->seiProcessRule->__invoke($input->message) !== null) {
                return $this->mainMenu($input->phone);
            }

            $municipality = $this->municipalityExtractor->extract($input->message);

            if ($municipality !== null) {
                $this->conversationState->put($input->phone, new WhatsappConversationStateDTO(
                    route: self::MunicipalityDisambiguationRoute,
                    municipality: $municipality,
                ));

                return $this->coreResponseFormatter->municipalityDisambiguation($municipality);
            }

            return $this->mainMenu($input->phone);
        } catch (RateLimitException) {
            return $this->responseFormatter->rateLimited();
        } catch (ConnectException) {
            return $this->responseFormatter->dataSourceUnavailable();
        } catch (GoogleServiceException $googleServiceException) {
            report($googleServiceException);

            return $this->responseFormatter->dataSourceUnavailable();
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->responseFormatter->error();
        }
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    private function processState(ReceivedMessageInputDTO $input, WhatsappConversationStateDTO $state): array
    {
        return match ($state->route) {
            self::MunicipalityDisambiguationRoute => $this->processMunicipalityDisambiguation($input, $state),
            self::BuildPanelRoute => $this->processBuildPanel($input),
            self::ContractMenuRoute => $this->processContractMenu($input),
            self::ContractSearchRoute => $this->processContractSearch($input, $state),
            default => $this->mainMenu($input->phone),
        };
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    private function processMunicipalityDisambiguation(
        ReceivedMessageInputDTO $input,
        WhatsappConversationStateDTO $state,
    ): array {
        if ($this->isOption($input->message, '0')) {
            return $this->mainMenu($input->phone);
        }

        if ($this->isOption($input->message, '1') && $state->municipality !== null) {
            $result = $this->buildPanel->process($state->municipality);
            $this->conversationState->forget($input->phone);

            return $result;
        }

        if ($this->isOption($input->message, '2') && $state->municipality !== null) {
            $result = $this->contract->search(4, $state->municipality);
            $this->conversationState->put($input->phone, new WhatsappConversationStateDTO(
                route: self::ContractMenuRoute,
            ));

            return $result;
        }

        return $this->coreResponseFormatter->municipalityDisambiguation((string) $state->municipality);
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    private function processBuildPanel(ReceivedMessageInputDTO $input): array
    {
        if ($this->isOption($input->message, '0')) {
            return $this->mainMenu($input->phone);
        }

        $result = $this->buildPanel->process($input->message);
        $this->conversationState->forget($input->phone);

        return $result;
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    private function processContractMenu(ReceivedMessageInputDTO $input): array
    {
        if ($this->isOption($input->message, '0')) {
            return $this->mainMenu($input->phone);
        }

        $option = $this->menuOption($input->message);

        if ($option === null) {
            return $this->contract->searchPrompt(0);
        }

        $this->conversationState->put($input->phone, new WhatsappConversationStateDTO(
            route: self::ContractSearchRoute,
            contractOption: $option,
        ));

        return $this->contract->searchPrompt($option);
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    private function processContractSearch(
        ReceivedMessageInputDTO $input,
        WhatsappConversationStateDTO $state,
    ): array {
        if ($this->isOption($input->message, '0')) {
            $this->conversationState->put($input->phone, new WhatsappConversationStateDTO(
                route: self::ContractMenuRoute,
            ));

            return $this->contract->menu();
        }

        if ($state->contractOption === null) {
            return $this->contract->searchPrompt(0);
        }

        $result = $this->contract->search($state->contractOption, $input->message);
        $this->conversationState->put($input->phone, new WhatsappConversationStateDTO(
            route: self::ContractMenuRoute,
        ));

        return $result;
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    private function mainMenu(?string $phone): array
    {
        $this->conversationState?->forget($phone);

        return $this->coreResponseFormatter->mainMenu();
    }

    private function hasConversationIntegration(): bool
    {
        return $this->coreResponseFormatter !== null
            && $this->conversationState !== null
            && $this->contract !== null
            && $this->municipalityExtractor !== null
            && $this->seiProcessRule !== null;
    }

    private function isOption(string $message, string $option): bool
    {
        return trim($message) === $option;
    }

    private function menuOption(string $message): ?int
    {
        $option = trim($message);

        return in_array($option, ['1', '2', '3', '4'], true) ? (int) $option : null;
    }
}
