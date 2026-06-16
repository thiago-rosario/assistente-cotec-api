from domain.whatsapp_message import WhatsAppMessage
from services.selenium_element_finder import SeleniumElementFinder
from services.whatsapp_chat_header_reader import WhatsAppChatHeaderReader
from services.whatsapp_locators import CUSTOMER_MESSAGE_FALLBACK_LOCATORS
from services.whatsapp_message_extractor import (
    ExtractedWhatsAppMessage,
    WhatsAppMessageExtractor,
    WhatsAppMessageSnapshot,
)
from services.whatsapp_message_state import WhatsAppMessageState


class WhatsAppOpenChatReader:
    MESSAGE_SCAN_LIMIT = 50

    def __init__(
        self,
        header_reader: WhatsAppChatHeaderReader,
        message_extractor: WhatsAppMessageExtractor,
        message_state: WhatsAppMessageState,
        element_finder: SeleniumElementFinder,
    ) -> None:
        self.header_reader = header_reader
        self.message_extractor = message_extractor
        self.message_state = message_state
        self.element_finder = element_finder

    def read_new_customer_messages(self) -> tuple[WhatsAppMessage, ...]:
        if not self.header_reader.has_open_chat():
            return ()

        customer_contact = self.header_reader.get_customer_phone()
        snapshot = self.message_extractor.extract(
            message_limit=self.MESSAGE_SCAN_LIMIT,
        )

        if not snapshot.incoming_messages:
            return ()

        if self.message_state.needs_open_chat_baseline(customer_contact):
            self.message_state.baseline_open_chat(
                customer_contact,
                snapshot.incoming_messages,
            )

            return ()

        candidates = self.message_state.candidate_messages_for_open_chat(
            customer_contact=customer_contact,
            incoming_messages=snapshot.incoming_messages,
            incoming_after_last_outgoing=snapshot.incoming_after_last_outgoing,
        )
        new_messages = self.message_state.filter_new_customer_messages(
            customer_contact,
            candidates,
        )

        return self._to_whatsapp_messages(customer_contact, new_messages)

    def read_snapshot(self) -> WhatsAppMessageSnapshot:
        return self.message_extractor.extract()

    def read_recent_customer_messages(
        self,
        limit: int | None = None,
    ) -> tuple[str, ...]:
        messages = self.message_extractor.read_recent_customer_messages(limit)

        if messages:
            return messages

        fallback_message = self.element_finder.wait_for_text(
            "customer_message",
            CUSTOMER_MESSAGE_FALLBACK_LOCATORS,
        )

        return (fallback_message,) if fallback_message else ()

    def read_last_customer_message(self) -> str:
        customer_message = self.message_extractor.read_last_customer_message()

        if customer_message:
            return customer_message

        return self.element_finder.wait_for_text(
            "customer_message",
            CUSTOMER_MESSAGE_FALLBACK_LOCATORS,
        )

    def _to_whatsapp_messages(
        self,
        customer_contact: str,
        messages: tuple[ExtractedWhatsAppMessage, ...],
    ) -> tuple[WhatsAppMessage, ...]:
        return tuple(
            WhatsAppMessage(
                customer_contact=customer_contact,
                content=message.text,
                external_id=self.message_state.message_key(customer_contact, message),
                received_at=message.timestamp or None,
            )
            for message in messages
        )
