import sys
import unittest
from pathlib import Path


PYTHON_APP_PATH = Path(__file__).resolve().parents[2] / "src/Core/Infra/External/Python"
sys.path.insert(0, str(PYTHON_APP_PATH))

from services.whatsapp_service import WhatsAppService  # noqa: E402


class FakeSelectors:
    def get(self, selector_key: str) -> str:
        return selector_key


class FakeDriver:
    def __init__(self, message: str = "") -> None:
        self.message = message
        self.script_calls = []

    def execute_script(self, script: str, *args):
        if args:
            self.script_calls.append((script, args))

            return None

        return self.message


class FakeMessageBox:
    def __init__(self) -> None:
        self.sent_keys = []
        self.was_clicked = False

    def click(self) -> None:
        self.was_clicked = True

    def send_keys(self, value: str) -> None:
        self.sent_keys.append(value)


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
