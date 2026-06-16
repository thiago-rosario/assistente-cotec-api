import sys
import unittest
from pathlib import Path


PYTHON_APP_PATH = Path(__file__).resolve().parents[2] / "src/Core/Infra/External/Python"
sys.path.insert(0, str(PYTHON_APP_PATH))

from services.whatsapp_message_extractor import (  # noqa: E402
    ExtractedWhatsAppMessage,
    WhatsAppMessageExtractor,
    WhatsAppMessageSnapshot,
)
from services.whatsapp_message_state import WhatsAppMessageState  # noqa: E402
from services.whatsapp_service import WhatsAppService  # noqa: E402


class FakeSelectors:
    def get(self, selector_key: str) -> str:
        return selector_key


class FakeDriver:
    def __init__(self, script_result: object) -> None:
        self.script_result = script_result

    def execute_script(self, script: str, *args: object) -> object:
        return self.script_result


class FakeHeaderReader:
    def __init__(self, customer_contact: str = "Thiago") -> None:
        self.customer_contact = customer_contact

    def has_whatsapp_loaded(self) -> bool:
        return True

    def has_open_chat(self) -> bool:
        return True

    def get_customer_phone(self) -> str:
        return self.customer_contact


class FakeChatListReader:
    def __init__(self, opened: bool, unread_count: int) -> None:
        self.opened = opened
        self.last_opened_unread_count = unread_count
        self.open_calls = 0

    def open_unread_chat(self) -> bool:
        self.open_calls += 1

        return self.opened


class FakeOpenChatReader:
    def __init__(self, messages: tuple = ()) -> None:
        self.messages = messages

    def read_new_customer_messages(self) -> tuple:
        return self.messages

    def read_recent_customer_messages(self, limit: int | None = None) -> tuple[str, ...]:
        return ()

    def read_last_customer_message(self) -> str:
        return ""


class FakeMessageExtractor:
    def __init__(self, snapshot: WhatsAppMessageSnapshot) -> None:
        self.snapshot = snapshot

    def extract(self) -> WhatsAppMessageSnapshot:
        return self.snapshot

    def read_recent_customer_messages(
        self,
        limit: int | None = None,
        only_after_last_outgoing: bool = False,
    ) -> tuple[str, ...]:
        messages = self.snapshot.incoming_after_last_outgoing

        if not messages and not only_after_last_outgoing:
            messages = self.snapshot.incoming_messages

        texts = tuple(message.text for message in messages)

        return texts[-limit:] if limit else texts

    def read_last_customer_message(self) -> str:
        messages = self.read_recent_customer_messages(limit=1)

        return messages[-1] if messages else ""


class FakeMessageSender:
    def __init__(self) -> None:
        self.sent_messages: list[str] = []

    def send_message(
        self,
        content: str,
        customer_contact: str | None = None,
    ) -> bool:
        self.sent_messages.append(content)

        return True


class WhatsAppRefactorFlowTest(unittest.TestCase):
    def test_extracts_structured_snapshot_from_single_dom_script_result(self) -> None:
        extractor = WhatsAppMessageExtractor(
            FakeDriver(
                {
                    "messages": (
                        {
                            "direction": "outgoing",
                            "text": "Resposta do bot",
                            "key": "true_bot",
                        },
                        {
                            "direction": "incoming",
                            "text": "ANDARAÍ",
                            "key": "false_customer",
                            "timestamp": "15/06/2026, 15:01:37",
                        },
                    )
                }
            )
        )

        snapshot = extractor.extract()

        self.assertEqual(snapshot.outgoing_texts, ("Resposta do bot",))
        self.assertEqual(snapshot.incoming_texts, ("ANDARAÍ",))
        self.assertEqual(snapshot.incoming_after_last_outgoing_texts, ("ANDARAÍ",))

    def test_service_reads_only_unread_count_from_badged_chat(self) -> None:
        message_state = WhatsAppMessageState()
        snapshot = WhatsAppMessageSnapshot(
            incoming_messages=(
                ExtractedWhatsAppMessage("incoming", "old", "false_old"),
                ExtractedWhatsAppMessage("incoming", "new 1", "false_new_1"),
                ExtractedWhatsAppMessage("incoming", "new 2", "false_new_2"),
            ),
            conversation_count=3,
        )
        service = WhatsAppService(
            driver=FakeDriver({}),
            selectors=FakeSelectors(),
            header_reader=FakeHeaderReader(),
            chat_list_reader=FakeChatListReader(opened=True, unread_count=2),
            message_extractor=FakeMessageExtractor(snapshot),
            open_chat_reader=FakeOpenChatReader(),
            message_sender=FakeMessageSender(),
            message_state=message_state,
        )

        messages = service.read_unread_messages()

        self.assertEqual(tuple(message.content for message in messages), ("new 1", "new 2"))
        self.assertEqual(messages[0].external_id, "Thiago|false_new_1")

    def test_state_filters_bot_reply_misclassified_as_incoming(self) -> None:
        message_state = WhatsAppMessageState()
        message_state.remember_sent_message("Resposta do bot")
        candidates = (
            ExtractedWhatsAppMessage("incoming", "Resposta do bot", "false_bot"),
            ExtractedWhatsAppMessage("incoming", "Mensagem real", "false_customer"),
        )

        new_messages = message_state.filter_new_customer_messages(
            "Thiago",
            candidates,
        )

        self.assertEqual(
            tuple(message.text for message in new_messages),
            ("Mensagem real",),
        )


if __name__ == "__main__":
    unittest.main()
