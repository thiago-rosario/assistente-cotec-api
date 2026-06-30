from domain.whatsapp_message import WhatsAppMessage


class WhatsAppProcessingTracker:
    def __init__(self) -> None:
        self._message_keys: set[str] = set()

    def has_pending_messages(self) -> bool:
        return bool(self._message_keys)

    def begin(self, messages: tuple[WhatsAppMessage, ...]) -> None:
        for message in messages:
            message_key = self._message_key(
                message.customer_contact,
                message.external_id,
            )

            if message_key:
                self._message_keys.add(message_key)

    def finish(
        self,
        customer_contact: str | None,
        external_id: str | None,
    ) -> None:
        message_key = self._message_key(customer_contact, external_id)

        if message_key:
            self._message_keys.discard(message_key)

    def _message_key(
        self,
        customer_contact: str | None,
        external_id: str | None,
    ) -> str:
        if not external_id:
            return ""

        return f"{customer_contact or ''}|{external_id}"
