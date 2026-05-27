import time
from collections.abc import Callable

from application.process_unread_message import ProcessUnreadMessageUseCase
from domain.whatsapp_message import WhatsAppMessage


def default_error_formatter(error: Exception) -> str:
    return f"Erro inesperado: {error}"


class WhatsAppBot:
    def __init__(
        self,
        process_unread_message: ProcessUnreadMessageUseCase,
        interval_seconds: int,
        output: Callable[[str], None] = print,
        error_formatter: Callable[[Exception], str] = default_error_formatter,
        message_formatter: Callable[[WhatsAppMessage], str] | None = None,
    ) -> None:
        self.process_unread_message = process_unread_message
        self.interval_seconds = interval_seconds
        self.output = output
        self.error_formatter = error_formatter
        self.message_formatter = message_formatter

    def run(self) -> None:
        self.output("Aguardando login no WhatsApp Web...")

        while True:
            self._process_next_message()
            time.sleep(self.interval_seconds)

    def _process_next_message(self) -> None:
        try:
            result = self.process_unread_message.execute()

            if self.message_formatter and result.whatsapp_message:
                self.output(self.message_formatter(result.whatsapp_message))
                return

            for message in result.messages:
                self.output(message)
        except Exception as error:
            self.output(self.error_formatter(error))
