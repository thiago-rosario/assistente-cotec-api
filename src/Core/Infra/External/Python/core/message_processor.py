import logging
from collections.abc import Callable
from typing import Any

from application.process_unread_message import (
    ProcessUnreadMessageResult,
    ProcessUnreadMessageUseCase,
)
from domain.whatsapp_message import WhatsAppMessage
from domain.whatsapp_selectors import WhatsAppSelectors
from services.whatsapp_message_state import WhatsAppMessageState
from services.whatsapp_service import WhatsAppService


def default_output(message: str) -> None:
    print(message, flush=True)


class MessageProcessor:
    def __init__(
        self,
        selectors: WhatsAppSelectors,
        message_state: WhatsAppMessageState,
        output: Callable[[str], None] = default_output,
        message_formatter: Callable[[WhatsAppMessage], str] | None = None,
        service_factory: Callable[..., WhatsAppService] = WhatsAppService,
        logger: logging.Logger | None = None,
    ) -> None:
        self.selectors = selectors
        self.message_state = message_state
        self.output = output
        self.message_formatter = message_formatter
        self.service_factory = service_factory
        self.logger = logger or logging.getLogger(__name__)
        self.whatsapp_service: WhatsAppService | None = None
        self.process_unread_message: ProcessUnreadMessageUseCase | None = None

    def attach_driver(self, driver: Any) -> None:
        self.whatsapp_service = self.service_factory(
            driver,
            self.selectors,
            message_state=self.message_state,
        )
        self.process_unread_message = ProcessUnreadMessageUseCase(
            self.whatsapp_service,
        )
        self.logger.info("Serviço do WhatsApp vinculado ao driver atual.")

    def process(self) -> ProcessUnreadMessageResult:
        if self.process_unread_message is None:
            raise RuntimeError("Processador de mensagens sem driver vinculado.")

        result = self.process_unread_message.execute()
        self._emit_status_messages()
        self._emit_result(result)

        return result

    def send_message(
        self,
        content: str,
        customer_contact: str | None = None,
    ) -> bool:
        if self.whatsapp_service is None:
            self.logger.error(
                "Resposta não enviada: serviço do WhatsApp ainda não foi iniciado."
            )

            return False

        return self.whatsapp_service.send_message(content, customer_contact)

    def has_whatsapp_loaded(self) -> bool:
        if self.whatsapp_service is None:
            return False

        return self.whatsapp_service.has_whatsapp_loaded()

    def _emit_status_messages(self) -> None:
        if self.whatsapp_service is None:
            return

        for message in self.whatsapp_service.pull_status_messages():
            self.output(message)

    def _emit_result(self, result: ProcessUnreadMessageResult) -> None:
        if self.message_formatter and result.whatsapp_messages:
            for whatsapp_message in result.whatsapp_messages:
                self.output(self.message_formatter(whatsapp_message))

            return

        for message in result.messages:
            self.output(message)
