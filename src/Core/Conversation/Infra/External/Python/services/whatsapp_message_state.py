import hashlib
import json
from pathlib import Path
from typing import Any

from services.whatsapp_message_extractor import ExtractedWhatsAppMessage


class WhatsAppMessageState:
    def __init__(self, state_path: Path | None = None) -> None:
        self.state_path = state_path
        self._seen_by_contact: dict[str, tuple[str, ...]] = {}
        self._processed_keys: set[str] = set()
        self._sent_messages: tuple[str, ...] = ()
        self._baseline_contacts: set[str] = set()

        self._load()

    @property
    def seen_by_contact(self) -> dict[str, tuple[str, ...]]:
        return self._seen_by_contact

    def needs_open_chat_baseline(self, customer_contact: str) -> bool:
        return (
            customer_contact not in self._baseline_contacts
            and customer_contact not in self._seen_by_contact
        )

    def baseline_open_chat(
        self,
        customer_contact: str,
        messages: tuple[ExtractedWhatsAppMessage, ...],
    ) -> None:
        self._baseline_contacts.add(customer_contact)
        self.remember_seen(customer_contact, messages)

    def remember_seen(
        self,
        customer_contact: str,
        messages: tuple[ExtractedWhatsAppMessage, ...],
    ) -> None:
        current_messages = list(self._seen_by_contact.get(customer_contact, ()))

        for message in messages:
            if self.was_sent_by_bot(message.text):
                continue

            if message.text not in current_messages:
                current_messages.append(message.text)

            self._processed_keys.add(self.message_key(customer_contact, message))

        self._seen_by_contact[customer_contact] = tuple(current_messages)
        self._save()

    def remember_sent_message(self, content: str) -> None:
        content = content.strip()

        if not content:
            return

        messages = [
            message
            for message in self._sent_messages
            if message != content
        ]
        messages.append(content)
        self._sent_messages = tuple(messages[-50:])
        self._save()

    def filter_new_customer_messages(
        self,
        customer_contact: str,
        messages: tuple[ExtractedWhatsAppMessage, ...],
    ) -> tuple[ExtractedWhatsAppMessage, ...]:
        new_messages = []

        for message in messages:
            if not message.text.strip() or self.was_sent_by_bot(message.text):
                continue

            message_key = self.message_key(customer_contact, message)

            if message_key in self._processed_keys:
                continue

            new_messages.append(message)

        filtered_messages = tuple(new_messages)
        self.remember_seen(customer_contact, filtered_messages)

        return filtered_messages

    def candidate_messages_for_open_chat(
        self,
        customer_contact: str,
        incoming_messages: tuple[ExtractedWhatsAppMessage, ...],
        incoming_after_last_outgoing: tuple[ExtractedWhatsAppMessage, ...],
    ) -> tuple[ExtractedWhatsAppMessage, ...]:
        if incoming_after_last_outgoing:
            return incoming_after_last_outgoing

        previous_texts = self._seen_by_contact.get(customer_contact, ())
        current_texts = tuple(message.text for message in incoming_messages)
        new_texts = self.diff_new_messages(previous_texts, current_texts)

        return tuple(
            message
            for message in incoming_messages
            if message.text in new_texts
        )

    def diff_new_messages(
        self,
        previous_messages: tuple[str, ...],
        current_messages: tuple[str, ...],
    ) -> tuple[str, ...]:
        if not previous_messages:
            return current_messages

        max_overlap = min(len(previous_messages), len(current_messages))

        for overlap in range(max_overlap, 0, -1):
            if previous_messages[-overlap:] == current_messages[:overlap]:
                return current_messages[overlap:]

        growth = len(current_messages) - len(previous_messages)

        if growth <= 0:
            return ()

        return current_messages[-growth:]

    def was_sent_by_bot(self, content: str) -> bool:
        return content.strip() in self._sent_messages

    def message_key(
        self,
        customer_contact: str,
        message: ExtractedWhatsAppMessage,
    ) -> str:
        key_source = message.key or message.timestamp

        if not key_source:
            key_source = hashlib.sha1(message.text.encode("utf-8")).hexdigest()

        return f"{customer_contact}|{key_source}"

    def is_processed(
        self,
        customer_contact: str,
        message: ExtractedWhatsAppMessage,
    ) -> bool:
        return self.message_key(customer_contact, message) in self._processed_keys

    def _load(self) -> None:
        if self.state_path is None or not self.state_path.exists():
            return

        try:
            data = json.loads(self.state_path.read_text(encoding="utf-8"))
        except (OSError, json.JSONDecodeError):
            return

        if not isinstance(data, dict):
            return

        self._seen_by_contact = self._tuple_dict(data.get("seen_by_contact"))
        self._processed_keys = set(self._string_tuple(data.get("processed_keys")))
        self._sent_messages = self._string_tuple(data.get("sent_messages"))
        self._baseline_contacts = set(self._string_tuple(data.get("baseline_contacts")))

    def _save(self) -> None:
        if self.state_path is None:
            return

        data = {
            "seen_by_contact": self._seen_by_contact,
            "processed_keys": sorted(self._processed_keys),
            "sent_messages": self._sent_messages,
            "baseline_contacts": sorted(self._baseline_contacts),
        }

        try:
            self.state_path.parent.mkdir(parents=True, exist_ok=True)
            self.state_path.write_text(
                json.dumps(data, ensure_ascii=False, indent=2),
                encoding="utf-8",
            )
        except OSError:
            return

    def _tuple_dict(self, value: Any) -> dict[str, tuple[str, ...]]:
        if not isinstance(value, dict):
            return {}

        return {
            str(key): self._string_tuple(messages)
            for key, messages in value.items()
        }

    def _string_tuple(self, value: Any) -> tuple[str, ...]:
        if not isinstance(value, (list, tuple)):
            return ()

        return tuple(str(item) for item in value if str(item).strip())
