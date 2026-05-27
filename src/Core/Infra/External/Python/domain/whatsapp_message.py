from dataclasses import dataclass


@dataclass(frozen=True)
class WhatsAppMessage:
    customer_contact: str
    content: str

    def to_bridge_payload(self) -> dict[str, str]:
        return {
            "customer_contact": self.customer_contact,
            "content": self.content,
            "source": "python-whatsapp",
        }
