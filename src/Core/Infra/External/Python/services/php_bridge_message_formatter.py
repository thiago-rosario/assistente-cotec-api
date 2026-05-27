import json

from domain.whatsapp_message import WhatsAppMessage


class PhpBridgeMessageFormatter:
    def __call__(self, message: WhatsAppMessage) -> str:
        return json.dumps(
            {
                "type": "received_message",
                "payload": message.to_bridge_payload(),
            },
            ensure_ascii=False,
        )
