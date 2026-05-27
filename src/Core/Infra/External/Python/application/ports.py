from typing import Protocol

from domain.whatsapp_message import WhatsAppMessage


class WhatsAppGateway(Protocol):
    def read_last_unread_message(self) -> WhatsAppMessage | None:
        ...

    def has_whatsapp_loaded(self) -> bool:
        ...
