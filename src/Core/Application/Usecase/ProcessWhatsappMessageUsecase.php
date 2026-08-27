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
use App\Core\Application\Trait\BuildsWhatsappConversationResponseTrait;
use App\Core\Application\Trait\HandlesWhatsappProcessingErrorsTrait;
use App\Core\Application\Trait\RoutesWhatsappConversationTrait;
use Google\Service\Exception as GoogleServiceException;
use GuzzleHttp\Exception\ConnectException;
use OpenAI\Exceptions\RateLimitException;
use Throwable;

class ProcessWhatsappMessageUsecase implements ProcessWhatsappMessageUsecaseInterface
{
    use BuildsWhatsappConversationResponseTrait;
    use HandlesWhatsappProcessingErrorsTrait;
    use RoutesWhatsappConversationTrait;

    private const string MunicipalityDisambiguationRoute = 'municipality_disambiguation';

    private const string BuildPanelRoute = 'build_panel';

    private const string ContractMenuRoute = 'contract_menu';

    private const string ContractSearchRoute = 'contract_search';

    private const string PostQueryActionRoute = 'post_query_action';

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
            $hasConversationIntegration = $this->hasConversationIntegration();
            $state = $hasConversationIntegration
                ? $this->conversationState->get($input->phone)
                : null;

            if ($this->isCloseCommand($input->message)) {
                return $this->closeConversation($input->phone);
            }

            if ($state?->route === self::PostQueryActionRoute) {
                return $this->processState($input, $state);
            }

            if (trim($input->message) === '') {
                return $this->coreResponseFormatter?->unsupportedMessageContent()
                    ?? $this->responseFormatter->unsupportedMessageContent();
            }

            if ($this->greetingMatcher->matches($input->message)) {
                if (! $hasConversationIntegration) {
                    return $this->responseFormatter->greeting();
                }

                $this->conversationState?->forget($input->phone);

                return $this->coreResponseFormatter->mainMenu();
            }

            if (! $hasConversationIntegration) {
                return $this->buildPanel->process($input->message);
            }

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
        } catch (RateLimitException $exception) {
            $this->logProcessingException('whatsapp_message_rate_limited', $input, $exception, 'warning');
            $this->clearConversationState($input->phone);

            return $this->responseFormatter->rateLimited();
        } catch (ConnectException $exception) {
            $this->logProcessingException('whatsapp_message_data_source_unavailable', $input, $exception, 'warning');
            $this->clearConversationState($input->phone);

            return $this->responseFormatter->dataSourceUnavailable();
        } catch (GoogleServiceException $googleServiceException) {
            report($googleServiceException);
            $this->logProcessingException(
                'whatsapp_message_google_service_failed',
                $input,
                $googleServiceException,
                'error',
            );
            $this->clearConversationState($input->phone);

            return $this->responseFormatter->dataSourceUnavailable();
        } catch (Throwable $throwable) {
            report($throwable);
            $this->logProcessingException('whatsapp_message_processing_failed', $input, $throwable, 'error');
            $this->clearConversationState($input->phone);

            return $this->responseFormatter->error();
        }
    }
}
