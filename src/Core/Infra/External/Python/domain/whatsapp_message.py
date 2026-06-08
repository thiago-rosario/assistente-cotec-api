from dataclasses import dataclass


@dataclass(frozen=True)
class WhatsAppMessage:
    customer_contact: str
    content: str

    def to_bridge_payload(self) -> dict[str, str | bool]:
        return {
            "customer_contact": self.customer_contact,
            "content": self.content,
            "content_detected": bool(self.content.strip()),
            "source": "python-whatsapp",
        }
