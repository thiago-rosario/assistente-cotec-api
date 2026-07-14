from dataclasses import dataclass


@dataclass(frozen=True)
class WhatsAppMessage:
    customer_contact: str
    content: str
    external_id: str | None = None
    received_at: str | None = None

    def to_bridge_payload(self) -> dict[str, str | bool]:
        payload: dict[str, str | bool] = {
            "customer_contact": self.customer_contact,
            "content": self.content,
            "content_detected": bool(self.content.strip()),
            "source": "python-whatsapp",
        }

        if self.external_id:
            payload["external_id"] = self.external_id

        if self.received_at:
            payload["received_at"] = self.received_at
            payload["timestamp"] = self.received_at

        return payload
