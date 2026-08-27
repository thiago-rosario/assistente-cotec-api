<?php

declare(strict_types=1);

namespace App\Core\Application\Trait;

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\DTO\WhatsappConversationStateDTO;
use Illuminate\Support\Str;

/**
 * Routes messages through the stateful WhatsApp conversation flow.
 *
 * The composing use case provides the conversation services and response
 * builders referenced by these handlers. They remain properties of the use
 * case and are intentionally not duplicated in this trait.
 */
trait RoutesWhatsappConversationTrait
{
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
            self::PostQueryActionRoute => $this->processPostQueryAction($input, $state),
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

            return $this->finishQuery($input, $result);
        }

        if ($this->isOption($input->message, '2') && $state->municipality !== null) {
            $result = $this->contract->search(4, $state->municipality);

            if (! $this->isCompletedQuery($result)) {
                $this->conversationState->put($input->phone, new WhatsappConversationStateDTO(
                    route: self::ContractMenuRoute,
                ));

                return $result;
            }

            return $this->finishQuery($input, $result, 4);
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

        return $this->finishQuery($input, $result);
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

        if ($this->isCompletedQuery($result)) {
            return $this->finishQuery($input, $result, $state->contractOption);
        }

        $this->conversationState->put($input->phone, new WhatsappConversationStateDTO(
            route: self::ContractMenuRoute,
        ));

        return $result;
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    private function processPostQueryAction(
        ReceivedMessageInputDTO $input,
        WhatsappConversationStateDTO $state,
    ): array {
        if ($this->isOption($input->message, '0')) {
            return $this->closeConversation($input->phone);
        }

        if ($this->isOption($input->message, '1')) {
            if ($state->contractOption !== null) {
                $this->conversationState->put($input->phone, new WhatsappConversationStateDTO(
                    route: self::ContractSearchRoute,
                    contractOption: $state->contractOption,
                ));

                return $this->contract->searchPrompt($state->contractOption);
            }

            $this->conversationState->put($input->phone, new WhatsappConversationStateDTO(
                route: self::BuildPanelRoute,
            ));

            return $this->responseFormatter->greeting();
        }

        return $this->coreResponseFormatter->invalidPostQueryAction();
    }

    private function isOption(string $message, string $option): bool
    {
        return trim($message) === $option;
    }

    private function isCloseCommand(string $message): bool
    {
        $normalized = Str::of($message)
            ->ascii()
            ->lower()
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();

        return in_array($normalized, [
            'sair',
            'encerrar',
            'encerrar conversa',
            'finalizar',
            'finalizar conversa',
            'tchau',
            'ate mais',
        ], true);
    }

    private function menuOption(string $message): ?int
    {
        $option = trim($message);

        return in_array($option, ['1', '2', '3', '4'], true) ? (int) $option : null;
    }
}
