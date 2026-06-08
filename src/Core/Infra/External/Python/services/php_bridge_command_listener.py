import json
import sys
import threading
from collections.abc import Callable
from typing import Any, TextIO


class PhpBridgeCommandListener:
    def __init__(
        self,
        send_message: Callable[[str, str | None], bool],
        input_stream: TextIO = sys.stdin,
        output: Callable[[str], None] = print,
    ) -> None:
        self.send_message = send_message
        self.input_stream = input_stream
        self.output = output

    def start(self) -> threading.Thread:
        thread = threading.Thread(target=self.listen, daemon=True)
        thread.start()

        return thread

    def listen(self) -> None:
        for line in self.input_stream:
            command = self._decode_command(line)

            if not command or command.get("type") != "send_message":
                continue

            payload = command.get("payload", {})
            content = str(payload.get("content", "")).strip()
            customer_contact = payload.get("customer_contact")

            if not content:
                continue

            was_sent = self.send_message(
                content,
                str(customer_contact) if customer_contact else None,
            )

            if was_sent:
                self.output("Resposta enviada no WhatsApp.")
            else:
                self.output("Não foi possível enviar a resposta no WhatsApp.")

    def _decode_command(self, line: str) -> dict[str, Any] | None:
        try:
            command = json.loads(line)
        except json.JSONDecodeError:
            return None

        return command if isinstance(command, dict) else None
