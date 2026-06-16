import sys
import unittest
from pathlib import Path


PYTHON_APP_PATH = Path(__file__).resolve().parents[2] / "src/Core/Infra/External/Python"
sys.path.insert(0, str(PYTHON_APP_PATH))

from services.whatsapp_service import WhatsAppService  # noqa: E402


class FakeDriver:
    def __init__(self, script_result: object = ()) -> None:
        self.script_result = script_result
        self.scripts: list[str] = []

    def execute_script(self, script: str, *args: object) -> object:
        self.scripts.append(script)

        return self.script_result


class FakeSelectors:
    def get(self, selector_key: str) -> str:
        return selector_key


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


class WhatsAppServiceBugfixTest(unittest.TestCase):
    def test_does_not_emit_empty_payload_when_unread_text_was_not_read(self) -> None:
        service = NoTextWhatsAppService(
            driver=FakeDriver(),
            selectors=FakeSelectors(),
        )

        self.assertEqual(service.read_unread_messages(), ())

    def test_dom_reader_keeps_plain_text_nodes_as_message_containers(self) -> None:
        driver = FakeDriver(script_result=[])
        service = WhatsAppService(
            driver=driver,
            selectors=FakeSelectors(),
        )

        service._read_recent_customer_messages_from_dom()

        self.assertEqual(len(driver.scripts), 1)
        self.assertIn("[data-pre-plain-text]", driver.scripts[0])
        self.assertIn("const messageDirection = (element) =>", driver.scripts[0])
        self.assertIn("dataId.startsWith('true_')", driver.scripts[0])
        self.assertIn("dataId.startsWith('false_')", driver.scripts[0])
        self.assertIn("senderMatchesChatTitle(sender)", driver.scripts[0])
        self.assertIn(
            "element.hasAttribute('data-pre-plain-text')",
            driver.scripts[0],
        )
        self.assertIn("[data-id]", driver.scripts[0])
        self.assertIn(
            "|| element.closest('[data-pre-plain-text]') || element;",
            driver.scripts[0],
        )


if __name__ == "__main__":
    unittest.main()
