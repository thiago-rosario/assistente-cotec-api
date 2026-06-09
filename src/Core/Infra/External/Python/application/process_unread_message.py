from dataclasses import dataclass

from application.ports import WhatsAppGateway
from domain.whatsapp_message import WhatsAppMessage


@dataclass(frozen=True)
class ProcessUnreadMessageResult:
    messages: tuple[str, ...]
    whatsapp_messages: tuple[WhatsAppMessage, ...] = ()

    @property
    def whatsapp_message(self) -> WhatsAppMessage | None:
        return self.whatsapp_messages[-1] if self.whatsapp_messages else None


class ProcessUnreadMessageUseCase:
    def __init__(self, whatsapp_gateway: WhatsAppGateway) -> None:
        self.whatsapp_gateway = whatsapp_gateway

    def execute(self) -> ProcessUnreadMessageResult:
        whatsapp_messages = self.whatsapp_gateway.read_unread_messages()

        if whatsapp_messages:
            return ProcessUnreadMessageResult(
                messages=tuple(
                    line
                    for whatsapp_message in whatsapp_messages
                    for line in (
                        f"Mensagem recebida de: {whatsapp_message.customer_contact}",
                        f"Conteúdo da mensagem: {whatsapp_message.content}",
                    )
                ),
                whatsapp_messages=whatsapp_messages,
            )

        message = "Nenhuma mensagem nova encontrada."

        if not self.whatsapp_gateway.has_whatsapp_loaded():
            message += " Aguardando login ou carregamento do WhatsApp Web."

        return ProcessUnreadMessageResult(messages=(message,))
