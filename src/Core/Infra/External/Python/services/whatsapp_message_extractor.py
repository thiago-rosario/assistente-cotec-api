from dataclasses import dataclass
from functools import lru_cache
from pathlib import Path
from typing import Any

from selenium.common.exceptions import WebDriverException
from selenium.webdriver.remote.webdriver import WebDriver


@dataclass(frozen=True)
class ExtractedWhatsAppMessage:
    direction: str
    text: str
    key: str = ""
    timestamp: str = ""


@dataclass(frozen=True)
class WhatsAppMessageSnapshot:
    incoming_messages: tuple[ExtractedWhatsAppMessage, ...] = ()
    outgoing_messages: tuple[ExtractedWhatsAppMessage, ...] = ()
    incoming_after_last_outgoing: tuple[ExtractedWhatsAppMessage, ...] = ()
    conversation_count: int = 0

    @property
    def incoming_texts(self) -> tuple[str, ...]:
        return tuple(message.text for message in self.incoming_messages)

    @property
    def outgoing_texts(self) -> tuple[str, ...]:
        return tuple(message.text for message in self.outgoing_messages)

    @property
    def incoming_after_last_outgoing_texts(self) -> tuple[str, ...]:
        return tuple(message.text for message in self.incoming_after_last_outgoing)


@lru_cache(maxsize=1)
def extract_messages_script() -> str:
    script_path = Path(__file__).resolve().parent / "scripts" / "extract_messages.js"

    return script_path.read_text(encoding="utf-8")


class WhatsAppMessageExtractor:
    def __init__(self, driver: WebDriver) -> None:
        self.driver = driver

    def extract(self) -> WhatsAppMessageSnapshot:
        try:
            raw_snapshot = self.driver.execute_script(extract_messages_script())
        except WebDriverException:
            return WhatsAppMessageSnapshot()

        return self._normalize_snapshot(raw_snapshot)

    def read_recent_customer_messages(
        self,
        limit: int | None = None,
        only_after_last_outgoing: bool = False,
    ) -> tuple[str, ...]:
        snapshot = self.extract()
        messages = snapshot.incoming_after_last_outgoing

        if not messages and not only_after_last_outgoing:
            messages = snapshot.incoming_messages

        texts = tuple(message.text for message in messages if message.text.strip())

        if limit is not None and limit > 0:
            return texts[-limit:]

        return texts

    def read_last_customer_message(self) -> str:
        messages = self.read_recent_customer_messages(limit=1)

        return messages[-1] if messages else ""

    def _normalize_snapshot(self, raw_snapshot: Any) -> WhatsAppMessageSnapshot:
        if isinstance(raw_snapshot, list):
            incoming_messages = self._messages_from_values(raw_snapshot, "incoming")

            return WhatsAppMessageSnapshot(
                incoming_messages=incoming_messages,
                incoming_after_last_outgoing=(),
                conversation_count=len(incoming_messages),
            )

        if isinstance(raw_snapshot, str):
            message = self._message_from_text(raw_snapshot, "incoming")
            incoming_messages = (message,) if message else ()

            return WhatsAppMessageSnapshot(
                incoming_messages=incoming_messages,
                incoming_after_last_outgoing=(),
                conversation_count=len(incoming_messages),
            )

        if not isinstance(raw_snapshot, dict):
            return WhatsAppMessageSnapshot()

        all_messages = self._messages_from_values(raw_snapshot.get("messages"), "")

        if all_messages:
            incoming_messages = tuple(
                message for message in all_messages if message.direction == "incoming"
            )
            outgoing_messages = tuple(
                message for message in all_messages if message.direction == "outgoing"
            )
            incoming_after_last_outgoing = self._incoming_after_last_outgoing(
                all_messages
            )

            return WhatsAppMessageSnapshot(
                incoming_messages=incoming_messages,
                outgoing_messages=outgoing_messages,
                incoming_after_last_outgoing=incoming_after_last_outgoing,
                conversation_count=self._conversation_count(
                    raw_snapshot,
                    len(all_messages),
                ),
            )

        incoming_messages = self._messages_from_snapshot_key(
            raw_snapshot,
            object_key="incomingMessages",
            text_keys=("incomingTexts", "allIncomingTexts"),
            direction="incoming",
        )
        outgoing_messages = self._messages_from_snapshot_key(
            raw_snapshot,
            object_key="outgoingMessages",
            text_keys=("outgoingTexts",),
            direction="outgoing",
        )
        incoming_after_last_outgoing = self._messages_from_snapshot_key(
            raw_snapshot,
            object_key="incomingAfterLastOutgoingMessages",
            text_keys=("incomingAfterLastOutgoingTexts",),
            direction="incoming",
        )

        return WhatsAppMessageSnapshot(
            incoming_messages=incoming_messages,
            outgoing_messages=outgoing_messages,
            incoming_after_last_outgoing=incoming_after_last_outgoing,
            conversation_count=self._conversation_count(
                raw_snapshot,
                len(incoming_messages) + len(outgoing_messages),
            ),
        )

    def _messages_from_snapshot_key(
        self,
        snapshot: dict[str, Any],
        object_key: str,
        text_keys: tuple[str, ...],
        direction: str,
    ) -> tuple[ExtractedWhatsAppMessage, ...]:
        object_messages = self._messages_from_values(snapshot.get(object_key), direction)

        if object_messages:
            return object_messages

        for text_key in text_keys:
            text_messages = self._messages_from_values(snapshot.get(text_key), direction)

            if text_messages:
                return text_messages

        return ()

    def _messages_from_values(
        self,
        values: Any,
        direction: str,
    ) -> tuple[ExtractedWhatsAppMessage, ...]:
        if values is None:
            return ()

        if isinstance(values, (str, dict)):
            values = (values,)

        if not isinstance(values, (list, tuple)):
            return ()

        messages = [
            self._message_from_value(value, direction)
            for value in values
        ]

        return tuple(message for message in messages if message is not None)

    def _message_from_value(
        self,
        value: Any,
        fallback_direction: str,
    ) -> ExtractedWhatsAppMessage | None:
        if isinstance(value, str):
            return self._message_from_text(value, fallback_direction)

        if not isinstance(value, dict):
            return None

        text = str(value.get("text", "")).strip()

        if not text:
            return None

        return ExtractedWhatsAppMessage(
            direction=str(value.get("direction") or fallback_direction).strip(),
            text=text,
            key=str(value.get("key") or value.get("id") or "").strip(),
            timestamp=str(
                value.get("timestamp")
                or value.get("received_at")
                or value.get("receivedAt")
                or ""
            ).strip(),
        )

    def _message_from_text(
        self,
        text: str,
        direction: str,
    ) -> ExtractedWhatsAppMessage | None:
        text = text.strip()

        if not text:
            return None

        return ExtractedWhatsAppMessage(direction=direction, text=text)

    def _incoming_after_last_outgoing(
        self,
        messages: tuple[ExtractedWhatsAppMessage, ...],
    ) -> tuple[ExtractedWhatsAppMessage, ...]:
        last_outgoing_index = -1

        for index, message in enumerate(messages):
            if message.direction == "outgoing":
                last_outgoing_index = index

        return tuple(
            message
            for index, message in enumerate(messages)
            if message.direction == "incoming" and index > last_outgoing_index
        )

    def _conversation_count(
        self,
        snapshot: dict[str, Any],
        fallback: int,
    ) -> int:
        conversation_count = snapshot.get("conversationCount")

        if isinstance(conversation_count, int) and conversation_count >= 0:
            return conversation_count

        return fallback
