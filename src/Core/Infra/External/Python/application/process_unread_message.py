from dataclasses import dataclass

from application.ports import WhatsAppGateway


@dataclass(frozen=True)
class ProcessUnreadMessageResult:
    messages: tuple[str, ...]


class ProcessUnreadMessageUseCase:
    def __init__(self, whatsapp_gateway: WhatsAppGateway) -> None:
        self.whatsapp_gateway = whatsapp_gateway

    def execute(self) -> ProcessUnreadMessageResult:
        whatsapp_message = self.whatsapp_gateway.read_last_unread_message()

        if whatsapp_message:
            return ProcessUnreadMessageResult(
                messages=(
                    f"Mensagem recebida de: {whatsapp_message.customer_contact}",
                    f"Conteúdo da mensagem: {whatsapp_message.content}",
                )
            )

        message = "Nenhuma mensagem nova encontrada."

        if not self.whatsapp_gateway.has_whatsapp_loaded():
            message += " Aguardando login ou carregamento do WhatsApp Web."

        return ProcessUnreadMessageResult(messages=(message,))
