from services.whatsapp_chat_header_reader import WhatsAppChatHeaderReader
from services.whatsapp_message_extractor import WhatsAppMessageExtractor
from services.whatsapp_message_sender import WhatsAppMessageSender
from services.whatsapp_message_state import WhatsAppMessageState


class WhatsAppReplySender:
    MESSAGE_SCAN_LIMIT = 50

    def __init__(
        self,
        header_reader: WhatsAppChatHeaderReader,
        message_extractor: WhatsAppMessageExtractor,
        message_sender: WhatsAppMessageSender,
        message_state: WhatsAppMessageState,
    ) -> None:
        self.header_reader = header_reader
        self.message_extractor = message_extractor
        self.message_sender = message_sender
        self.message_state = message_state

    def send(
        self,
        content: str,
        customer_contact: str | None = None,
    ) -> bool:
        if not content.strip() or not self.header_reader.has_open_chat():
            return False

        customer_contact = customer_contact or self.header_reader.get_customer_phone()
        snapshot = self.message_extractor.extract(
            message_limit=self.MESSAGE_SCAN_LIMIT,
        )

        if snapshot.incoming_messages:
            self.message_state.remember_seen(
                customer_contact,
                snapshot.incoming_messages,
            )

        return self.message_sender.send_message(content, customer_contact)
