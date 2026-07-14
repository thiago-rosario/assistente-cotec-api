from collections.abc import Callable

from domain.whatsapp_message import WhatsAppMessage
from services.whatsapp_chat_header_reader import WhatsAppChatHeaderReader
from services.whatsapp_chat_list_reader import WhatsAppChatListReader
from services.whatsapp_message_extractor import (
    ExtractedWhatsAppMessage,
    WhatsAppMessageExtractor,
    WhatsAppMessageSnapshot,
)
from services.whatsapp_message_state import WhatsAppMessageState
from services.whatsapp_open_chat_reader import WhatsAppOpenChatReader


class WhatsAppUnreadMessageReader:
    MESSAGE_SCAN_LIMIT = 50

    def __init__(
        self,
        header_reader: WhatsAppChatHeaderReader,
        chat_list_reader: WhatsAppChatListReader,
        message_extractor: WhatsAppMessageExtractor,
        open_chat_reader: WhatsAppOpenChatReader,
        message_state: WhatsAppMessageState,
        record_status: Callable[[str], None],
    ) -> None:
        self.header_reader = header_reader
        self.chat_list_reader = chat_list_reader
        self.message_extractor = message_extractor
        self.open_chat_reader = open_chat_reader
        self.message_state = message_state
        self.record_status = record_status

    def read(self) -> tuple[WhatsAppMessage, ...]:
        open_chat_messages = self.open_chat_reader.read_new_customer_messages()

        if open_chat_messages:
            return open_chat_messages

        if not self.chat_list_reader.open_unread_chat():
            return ()

        customer_contact = self.header_reader.get_customer_phone()
        snapshot = self.message_extractor.extract(
            message_limit=self._unread_message_scan_limit(),
        )
        candidates = self._candidate_messages_from_unread_chat(snapshot)
        new_messages = self._filter_new_customer_messages(
            customer_contact,
            candidates,
        )

        return self._to_whatsapp_messages(customer_contact, new_messages)

    def _candidate_messages_from_unread_chat(
        self,
        snapshot: WhatsAppMessageSnapshot,
    ) -> tuple[ExtractedWhatsAppMessage, ...]:
        unread_count = max(self.chat_list_reader.last_opened_unread_count, 1)
        candidates = (
            snapshot.incoming_after_last_outgoing
            or snapshot.incoming_messages
        )

        return candidates[-unread_count:]

    def _unread_message_scan_limit(self) -> int:
        unread_count = max(self.chat_list_reader.last_opened_unread_count, 1)

        return max(unread_count + 10, self.MESSAGE_SCAN_LIMIT)

    def _filter_new_customer_messages(
        self,
        customer_contact: str,
        messages: tuple[ExtractedWhatsAppMessage, ...],
    ) -> tuple[ExtractedWhatsAppMessage, ...]:
        new_messages: list[ExtractedWhatsAppMessage] = []

        for message in messages:
            if not message.text.strip():
                continue

            if self.message_state.was_sent_by_bot(message.text):
                continue

            if self.message_state.is_processed(customer_contact, message):
                self.record_status(
                    "Mensagem ignorada por já ter sido processada: "
                    f"contato={customer_contact}, "
                    f"id={self.message_state.message_key(customer_contact, message)}, "
                    f'conteúdo="{message.text}"'
                )
                continue

            new_messages.append(message)

        filtered_messages = tuple(new_messages)
        self.message_state.remember_seen(customer_contact, filtered_messages)

        return filtered_messages

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
