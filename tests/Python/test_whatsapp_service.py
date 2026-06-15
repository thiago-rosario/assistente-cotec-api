import sys
import unittest
from pathlib import Path


PYTHON_APP_PATH = Path(__file__).resolve().parents[2] / "src/Core/Infra/External/Python"
sys.path.insert(0, str(PYTHON_APP_PATH))

from application.process_unread_message import ProcessUnreadMessageUseCase  # noqa: E402
from application.whatsapp_bot import WhatsAppBot  # noqa: E402
from domain.whatsapp_message import WhatsAppMessage  # noqa: E402
from services.php_bridge_message_formatter import PhpBridgeMessageFormatter  # noqa: E402
from services.whatsapp_service import WhatsAppService  # noqa: E402


class FakeSelectors:
    def get(self, selector_key: str) -> str:
        return selector_key


class FakeDriver:
    def __init__(self, message: object = "") -> None:
        self.message = message
        self.script_calls = []

    def execute_script(self, script: str, *args):
        if args:
            self.script_calls.append((script, args))

            return None

        return self.message


class FakeGateway:
    def __init__(self, messages: tuple[WhatsAppMessage, ...]) -> None:
        self.messages = messages

    def read_unread_messages(self) -> tuple[WhatsAppMessage, ...]:
        return self.messages

    def read_last_unread_message(self) -> WhatsAppMessage | None:
        return self.messages[-1] if self.messages else None

    def has_whatsapp_loaded(self) -> bool:
        return True

    def send_message(
        self,
        content: str,
        customer_contact: str | None = None,
    ) -> bool:
        return True


class FakeMessageBox:
    def __init__(self) -> None:
        self.sent_keys = []
        self.was_clicked = False

    def click(self) -> None:
        self.was_clicked = True

    def send_keys(self, value: str) -> None:
        self.sent_keys.append(value)


class NoTextWhatsAppService(WhatsAppService):
    def open_unread_chat(self) -> bool:
        return True

    def get_customer_phone(self) -> str:
        return "Thiago"

    def get_recent_customer_messages(
        self,
        limit: int | None = None,
    ) -> tuple[str, ...]:
        return ()


class SequencedWhatsAppService(WhatsAppService):
    def __init__(
        self,
        *,
        snapshots: tuple[tuple[tuple[str, ...], tuple[str, ...]], ...],
        unread_results: tuple[bool, ...],
        recent_messages: tuple[tuple[str, ...], ...] = (),
    ) -> None:
        super().__init__(
            driver=FakeDriver(),
            selectors=FakeSelectors(),
        )
        self.snapshots = list(snapshots)
        self.unread_results = list(unread_results)
        self.recent_messages = list(recent_messages)

    def open_unread_chat(self) -> bool:
        return self.unread_results.pop(0) if self.unread_results else False

    def has_open_chat(self) -> bool:
        return True

    def get_customer_phone(self) -> str:
        return "Thiago"

    def get_recent_customer_messages(
        self,
        limit: int | None = None,
    ) -> tuple[str, ...]:
        messages = self.recent_messages.pop(0) if self.recent_messages else ()

        if limit is not None and limit > 0:
            return messages[-limit:]

        return messages

    def _read_customer_message_snapshot_from_dom(
        self,
    ) -> tuple[tuple[str, ...], tuple[str, ...]]:
        return self.snapshots.pop(0) if self.snapshots else ((), ())


class WhatsAppServiceTest(unittest.TestCase):
    def test_reads_last_customer_message_from_dom_before_fallback(self) -> None:
        service = WhatsAppService(
            driver=FakeDriver("030.2647.2023.0170476-39"),
            selectors=FakeSelectors(),
        )

        self.assertEqual(
            service.get_last_customer_message(),
            "030.2647.2023.0170476-39",
        )

    def test_reads_recent_customer_messages_from_dom_before_fallback(self) -> None:
        service = WhatsAppService(
            driver=FakeDriver(["020.4487.2021.0009714-69", "ANDARAÍ"]),
            selectors=FakeSelectors(),
        )

        self.assertEqual(
            service.get_recent_customer_messages(limit=2),
            ("020.4487.2021.0009714-69", "ANDARAÍ"),
        )

    def test_reads_recent_customer_messages_after_last_outgoing_message(self) -> None:
        service = WhatsAppService(
            driver=FakeDriver(
                {
                    "allIncomingTexts": (
                        "020.4487.2021.0009714-69",
                        "ANDARAÍ",
                    ),
                    "incomingAfterLastOutgoingTexts": ("ANDARAÍ",),
                }
            ),
            selectors=FakeSelectors(),
        )

        self.assertEqual(
            service.get_recent_customer_messages(),
            ("ANDARAÍ",),
        )

    def test_reads_recent_visible_messages_regardless_of_sender(self) -> None:
        service = WhatsAppService(
            driver=FakeDriver(
                {
                    "allIncomingTexts": (),
                    "allConversationTexts": (
                        "Olá! Eu sou o assistente da COTEC.",
                        "Itacaré",
                    ),
                    "incomingAfterLastOutgoingTexts": (),
                }
            ),
            selectors=FakeSelectors(),
        )

        self.assertEqual(
            service.get_recent_customer_messages(limit=1),
            ("Itacaré",),
        )

    def test_keeps_unread_message_event_when_text_content_is_empty(self) -> None:
        service = NoTextWhatsAppService(
            driver=FakeDriver(),
            selectors=FakeSelectors(),
        )

        messages = service.read_unread_messages()

        self.assertEqual(len(messages), 1)
        self.assertEqual(messages[0].customer_contact, "Thiago")
        self.assertEqual(messages[0].content, "")

    def test_reads_new_customer_message_when_chat_is_already_open(self) -> None:
        service = SequencedWhatsAppService(
            snapshots=(
                (("020.4487.2021.0009714-69",), ()),
                (("020.4487.2021.0009714-69",), ()),
                (("020.4487.2021.0009714-69", "ANDARAÍ"), ()),
            ),
            unread_results=(True, False, False),
            recent_messages=(("020.4487.2021.0009714-69",),),
        )

        first_messages = service.read_unread_messages()
        repeated_messages = service.read_unread_messages()
        open_chat_messages = service.read_unread_messages()

        self.assertEqual(
            tuple(message.content for message in first_messages),
            ("020.4487.2021.0009714-69",),
        )
        self.assertEqual(repeated_messages, ())
        self.assertEqual(len(open_chat_messages), 1)
        self.assertEqual(open_chat_messages[0].customer_contact, "Thiago")
        self.assertEqual(open_chat_messages[0].content, "ANDARAÍ")

    def test_reads_open_chat_message_after_last_outgoing_on_first_scan(self) -> None:
        service = SequencedWhatsAppService(
            snapshots=(
                (
                    ("020.4487.2021.0009714-69", "ANDARAÍ"),
                    ("ANDARAÍ",),
                ),
            ),
            unread_results=(False,),
        )

        messages = service.read_unread_messages()

        self.assertEqual(len(messages), 1)
        self.assertEqual(messages[0].customer_contact, "Thiago")
        self.assertEqual(messages[0].content, "ANDARAÍ")

    def test_processes_every_unread_message_from_the_same_chat(self) -> None:
        use_case = ProcessUnreadMessageUseCase(
            FakeGateway(
                (
                    WhatsAppMessage("Thiago", "020.4487.2021.0009714-69"),
                    WhatsAppMessage("Thiago", "ANDARAÍ"),
                )
            )
        )

        result = use_case.execute()

        self.assertEqual(len(result.whatsapp_messages), 2)
        self.assertEqual(
            result.messages,
            (
                "Mensagem recebida de: Thiago",
                "Conteúdo da mensagem: 020.4487.2021.0009714-69",
                "Mensagem recebida de: Thiago",
                "Conteúdo da mensagem: ANDARAÍ",
            ),
        )

    def test_bot_outputs_one_bridge_event_per_unread_message(self) -> None:
        output: list[str] = []
        bot = WhatsAppBot(
            ProcessUnreadMessageUseCase(
                FakeGateway(
                    (
                        WhatsAppMessage("Thiago", "020.4487.2021.0009714-69"),
                        WhatsAppMessage("Thiago", "ANDARAÍ"),
                    )
                )
            ),
            interval_seconds=0,
            output=output.append,
            message_formatter=PhpBridgeMessageFormatter(),
        )

        bot._process_next_message()

        self.assertEqual(len(output), 2)
        self.assertIn('"content": "020.4487.2021.0009714-69"', output[0])
        self.assertIn('"content": "ANDARAÍ"', output[1])

    def test_types_non_bmp_characters_with_javascript_insert_text(self) -> None:
        driver = FakeDriver()
        service = WhatsAppService(
            driver=driver,
            selectors=FakeSelectors(),
        )
        message_box = FakeMessageBox()

        service._type_message(message_box, "Ola \U0001F600")

        self.assertTrue(message_box.was_clicked)
        self.assertEqual(message_box.sent_keys, ["\ue007"])
        self.assertEqual(len(driver.script_calls), 1)
        self.assertEqual(driver.script_calls[0][1], (message_box, "Ola \U0001F600"))


if __name__ == "__main__":
    unittest.main()
