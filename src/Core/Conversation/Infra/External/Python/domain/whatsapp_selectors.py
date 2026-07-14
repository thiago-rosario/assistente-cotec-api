from dataclasses import dataclass


@dataclass(frozen=True)
class WhatsAppSelectors:
    notification_badge: str
    customer_contact: str
    message_box: str
    customer_message: str
    message_box_alternative: str
    search_box: str

    def get(self, selector_key: str) -> str:
        return getattr(self, selector_key)
