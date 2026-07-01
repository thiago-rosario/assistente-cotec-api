import json
import sys
import threading
from collections.abc import Callable
from typing import Any, TextIO


class PhpBridgeCommandListener:
    def __init__(
        self,
        send_message: Callable[[str, str | None], bool],
        finish_processing_message: (
            Callable[[str | None, str | None], None] | None
        ) = None,
        input_stream: TextIO = sys.stdin,
        output: Callable[[str], None] = print,
    ) -> None:
        self.send_message = send_message
        self.finish_processing_message = finish_processing_message
        self.input_stream = input_stream
        self.output = output

    def start(self) -> threading.Thread:
        thread = threading.Thread(target=self.listen, daemon=True)
        thread.start()

        return thread

    def listen(self) -> None:
        for line in self.input_stream:
            command = self._decode_command(line)

            if not command:
                continue

            if command.get("type") == "send_message":
                self._handle_send_message(command)

                continue

            if command.get("type") == "message_processed":
                self._handle_message_processed(command)

    def _handle_send_message(self, command: dict[str, Any]) -> None:
        payload = command.get("payload", {})
        content = str(payload.get("content", "")).strip()
        customer_contact = payload.get("customer_contact")

        if not content:
            return

        try:
            was_sent = self.send_message(
                content,
                str(customer_contact) if customer_contact else None,
            )
        except Exception as error:
            self.output(f"Erro ao responder no WhatsApp: {error}")

            return

        if was_sent:
            self.output("Resposta enviada ao WhatsApp.")
        else:
            self.output("Erro ao responder no WhatsApp: envio recusado.")

    def _handle_message_processed(self, command: dict[str, Any]) -> None:
        if not self.finish_processing_message:
            return

        payload = command.get("payload", {})
        customer_contact = payload.get("customer_contact")
        external_id = payload.get("external_id")

        self.finish_processing_message(
            str(customer_contact) if customer_contact else None,
            str(external_id) if external_id else None,
        )

    def _decode_command(self, line: str) -> dict[str, Any] | None:
        try:
            command = json.loads(line)
        except json.JSONDecodeError:
            return None

        return command if isinstance(command, dict) else None
