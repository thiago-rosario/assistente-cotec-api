from dataclasses import dataclass


@dataclass(frozen=True)
class WhatsAppMessage:
    customer_contact: str
    content: str
