import sys
import threading
import unittest
from io import StringIO
from pathlib import Path

from selenium.common.exceptions import (
    StaleElementReferenceException,
    WebDriverException,
)
from selenium.webdriver.common.keys import Keys

PYTHON_APP_PATH = Path(__file__).resolve().parents[2] / "src/Core/Infra/External/Python"
sys.path.insert(0, str(PYTHON_APP_PATH))

from application.process_unread_message import ProcessUnreadMessageUseCase  # noqa: E402
from application.whatsapp_bot import WhatsAppBot  # noqa: E402
from domain.whatsapp_message import WhatsAppMessage  # noqa: E402
from services.php_bridge_command_listener import PhpBridgeCommandListener  # noqa: E402
from services.whatsapp_message_extractor import (  # noqa: E402
    ExtractedWhatsAppMessage,
    WhatsAppMessageExtractor,
    WhatsAppMessageSnapshot,
)
from services.whatsapp_message_sender import WhatsAppMessageSender  # noqa: E402
from services.whatsapp_message_state import WhatsAppMessageState  # noqa: E402
from services.whatsapp_service import WhatsAppService  # noqa: E402
from services.whatsapp_service_state import WhatsAppServiceState  # noqa: E402


class FakeSelectors:
    def get(self, selector_key: str) -> str:
        return selector_key


class FakeDriver:
    def __init__(self, script_result: object) -> None:
        self.script_result = script_result
        self.script_arguments: list[tuple[object, ...]] = []
        self.refresh_calls = 0

    def execute_script(self, script: str, *args: object) -> object:
        self.script_arguments.append(args)

        return self.script_result

    def refresh(self) -> None:
        self.refresh_calls += 1


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
        self.read_calls = 0

    def read_new_customer_messages(self) -> tuple:
        self.read_calls += 1

        return self.messages

    def read_recent_customer_messages(self, limit: int | None = None) -> tuple[str, ...]:
        return ()

    def read_last_customer_message(self) -> str:
        return ""


class FakeMessageExtractor:
    def __init__(self, snapshot: WhatsAppMessageSnapshot) -> None:
        self.snapshot = snapshot
        self.message_limits: list[int | None] = []

    def extract(self, message_limit: int | None = None) -> WhatsAppMessageSnapshot:
        self.message_limits.append(message_limit)

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


class NotifyingMessageSender(FakeMessageSender):
    def __init__(self) -> None:
        super().__init__()
        self.called = threading.Event()

    def send_message(
        self,
        content: str,
        customer_contact: str | None = None,
    ) -> bool:
        self.called.set()

        return super().send_message(content, customer_contact)


class BlockingOpenChatReader(FakeOpenChatReader):
    def __init__(self) -> None:
        super().__init__()
        self.entered = threading.Event()
        self.release = threading.Event()

    def read_new_customer_messages(self) -> tuple:
        self.entered.set()
        self.release.wait(1)

        return ()


class StaleClickMessageBox:
    def click(self) -> None:
        raise StaleElementReferenceException("stale message box")

    def send_keys(self, value: str) -> None:
        raise AssertionError("stale message box should not receive keys")


class WebDriverErrorClickMessageBox:
    def click(self) -> None:
        raise WebDriverException("message box was replaced")

    def send_keys(self, value: str) -> None:
        raise AssertionError("broken message box should not receive keys")


class WorkingMessageBox:
    def __init__(self) -> None:
        self.was_clicked = False
        self.sent_keys: list[str] = []

    def click(self) -> None:
        self.was_clicked = True

    def send_keys(self, value: str) -> None:
        self.sent_keys.append(value)


class SequencedElementFinder:
    def __init__(self, message_boxes: tuple[object, ...]) -> None:
        self.message_boxes = list(message_boxes)
        self.wait_calls = 0

    def build_locator(self, selector_key: str) -> tuple[str, str]:
        return "css selector", selector_key

    def wait_for_elements(
        self,
        locators: tuple[tuple[str, str], ...],
        timeout: int = 5,
    ) -> list[object]:
        self.wait_calls += 1

        if not self.message_boxes:
            return []

        return [self.message_boxes.pop(0)]


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

    def test_extractor_can_limit_dom_message_scan(self) -> None:
        driver = FakeDriver({})
        extractor = WhatsAppMessageExtractor(driver)

        extractor.extract(message_limit=25)

        self.assertEqual(driver.script_arguments, [({"messageLimit": 25},)])

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
        message_extractor = FakeMessageExtractor(snapshot)
        service = WhatsAppService(
            driver=FakeDriver({}),
            selectors=FakeSelectors(),
            header_reader=FakeHeaderReader(),
            chat_list_reader=FakeChatListReader(opened=True, unread_count=2),
            message_extractor=message_extractor,
            open_chat_reader=FakeOpenChatReader(),
            message_sender=FakeMessageSender(),
            message_state=message_state,
        )

        messages = service.read_unread_messages()

        self.assertEqual(tuple(message.content for message in messages), ("new 1", "new 2"))
        self.assertEqual(messages[0].external_id, "Thiago|false_new_1")
        self.assertEqual(message_extractor.message_limits, [50])

    def test_service_expands_scan_window_for_large_unread_badge(self) -> None:
        snapshot = WhatsAppMessageSnapshot(
            incoming_messages=(
                ExtractedWhatsAppMessage("incoming", "new", "false_new"),
            ),
            conversation_count=1,
        )
        message_extractor = FakeMessageExtractor(snapshot)
        service = WhatsAppService(
            driver=FakeDriver({}),
            selectors=FakeSelectors(),
            header_reader=FakeHeaderReader(),
            chat_list_reader=FakeChatListReader(opened=True, unread_count=75),
            message_extractor=message_extractor,
            open_chat_reader=FakeOpenChatReader(),
            message_sender=FakeMessageSender(),
            message_state=WhatsAppMessageState(),
        )

        service.read_unread_messages()

        self.assertEqual(message_extractor.message_limits, [85])

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

    def test_sender_retries_with_new_message_box_when_element_goes_stale(self) -> None:
        message_state = WhatsAppMessageState()
        working_message_box = WorkingMessageBox()
        element_finder = SequencedElementFinder(
            (StaleClickMessageBox(), working_message_box),
        )
        sender = WhatsAppMessageSender(
            driver=FakeDriver({}),
            header_reader=FakeHeaderReader(),
            element_finder=element_finder,
            message_state=message_state,
            max_send_attempts=2,
            retry_delay_seconds=0,
        )

        was_sent = sender.send_message("Resposta do bot")

        self.assertTrue(was_sent)
        self.assertEqual(element_finder.wait_calls, 2)
        self.assertTrue(working_message_box.was_clicked)
        self.assertEqual(working_message_box.sent_keys, ["Resposta do bot", Keys.ENTER])
        self.assertTrue(message_state.was_sent_by_bot("Resposta do bot"))

    def test_sender_retries_when_selenium_rejects_message_box_action(self) -> None:
        message_state = WhatsAppMessageState()
        working_message_box = WorkingMessageBox()
        element_finder = SequencedElementFinder(
            (WebDriverErrorClickMessageBox(), working_message_box),
        )
        sender = WhatsAppMessageSender(
            driver=FakeDriver({}),
            header_reader=FakeHeaderReader(),
            element_finder=element_finder,
            message_state=message_state,
            max_send_attempts=2,
            retry_delay_seconds=0,
        )

        was_sent = sender.send_message("Resposta do bot")

        self.assertTrue(was_sent)
        self.assertEqual(element_finder.wait_calls, 2)
        self.assertTrue(working_message_box.was_clicked)
        self.assertEqual(working_message_box.sent_keys, ["Resposta do bot", Keys.ENTER])
        self.assertTrue(message_state.was_sent_by_bot("Resposta do bot"))

    def test_service_serializes_read_and_send_on_same_driver(self) -> None:
        open_chat_reader = BlockingOpenChatReader()
        message_sender = NotifyingMessageSender()
        service = WhatsAppService(
            driver=FakeDriver({}),
            selectors=FakeSelectors(),
            header_reader=FakeHeaderReader(),
            chat_list_reader=FakeChatListReader(opened=False, unread_count=1),
            message_extractor=FakeMessageExtractor(WhatsAppMessageSnapshot()),
            open_chat_reader=open_chat_reader,
            message_sender=message_sender,
            message_state=WhatsAppMessageState(),
        )

        read_thread = threading.Thread(target=service.read_unread_messages)
        read_thread.start()
        self.assertTrue(open_chat_reader.entered.wait(1))

        send_thread = threading.Thread(
            target=lambda: service.send_message("Resposta do bot", "Thiago"),
        )
        send_thread.start()

        self.assertFalse(message_sender.called.wait(0.05))

        open_chat_reader.release.set()
        read_thread.join(1)
        send_thread.join(1)

        self.assertFalse(read_thread.is_alive())
        self.assertFalse(send_thread.is_alive())
        self.assertEqual(message_sender.sent_messages, ["Resposta do bot"])

    def test_bridge_defers_new_reads_until_message_processed_ack(self) -> None:
        open_chat_reader = FakeOpenChatReader(
            (WhatsAppMessage("Thiago", "ANDARAÍ", "msg-1"),)
        )
        service = WhatsAppService(
            driver=FakeDriver({}),
            selectors=FakeSelectors(),
            header_reader=FakeHeaderReader(),
            chat_list_reader=FakeChatListReader(opened=False, unread_count=1),
            message_extractor=FakeMessageExtractor(WhatsAppMessageSnapshot()),
            open_chat_reader=open_chat_reader,
            message_sender=FakeMessageSender(),
            message_state=WhatsAppMessageState(),
        )
        output: list[str] = []
        use_case = ProcessUnreadMessageUseCase(service)
        bot = WhatsAppBot(
            use_case,
            interval_seconds=0,
            output=output.append,
            message_formatter=lambda message: f"event:{message.external_id}",
        )

        bot._process_next_message()

        self.assertEqual(output, ["event:msg-1"])
        self.assertEqual(service.state, WhatsAppServiceState.PROCESSING)

        open_chat_reader.messages = (WhatsAppMessage("Maria", "Nova", "msg-2"),)
        busy_result = use_case.execute()

        self.assertEqual(
            busy_result.messages,
            ("Bot ocupado aguardando processamento da resposta atual.",),
        )
        self.assertEqual(open_chat_reader.read_calls, 1)
        self.assertTrue(
            any(
                "aguardando processamento" in message
                for message in service.pull_status_messages()
            )
        )

        service.finish_processing_message("Thiago", "msg-1")

        self.assertEqual(service.state, WhatsAppServiceState.IDLE)
        self.assertEqual(
            service.read_unread_messages(),
            (WhatsAppMessage("Maria", "Nova", "msg-2"),),
        )

    def test_bridge_listener_marks_processed_and_survives_send_errors(self) -> None:
        sent_messages: list[tuple[str, str | None]] = []
        finished_messages: list[tuple[str | None, str | None]] = []
        output: list[str] = []

        def send_message(content: str, customer_contact: str | None = None) -> bool:
            if content == "falha":
                raise WebDriverException("driver unavailable")

            sent_messages.append((content, customer_contact))

            return True

        listener = PhpBridgeCommandListener(
            send_message,
            finish_processing_message=lambda customer_contact, external_id: (
                finished_messages.append((customer_contact, external_id))
            ),
            input_stream=StringIO(
                '{"type":"send_message","payload":'
                '{"content":"falha","customer_contact":"Thiago"}}\n'
                '{"type":"message_processed","payload":'
                '{"customer_contact":"Thiago","external_id":"msg-1"}}\n'
                '{"type":"send_message","payload":'
                '{"content":"ok","customer_contact":"Thiago"}}\n'
            ),
            output=output.append,
        )

        listener.listen()

        self.assertEqual(sent_messages, [("ok", "Thiago")])
        self.assertEqual(finished_messages, [("Thiago", "msg-1")])
        self.assertTrue(
            any(
                message.startswith("Erro ao responder no WhatsApp")
                for message in output
            )
        )
        self.assertIn("Resposta enviada ao WhatsApp.", output)

    def test_service_recovers_to_idle_when_read_hits_driver_error(self) -> None:
        class FailingOpenChatReader(FakeOpenChatReader):
            def read_new_customer_messages(self) -> tuple:
                self.read_calls += 1

                raise WebDriverException("DOM changed while reading")

        driver = FakeDriver({})
        service = WhatsAppService(
            driver=driver,
            selectors=FakeSelectors(),
            header_reader=FakeHeaderReader(),
            chat_list_reader=FakeChatListReader(opened=False, unread_count=1),
            message_extractor=FakeMessageExtractor(WhatsAppMessageSnapshot()),
            open_chat_reader=FailingOpenChatReader(),
            message_sender=FakeMessageSender(),
            message_state=WhatsAppMessageState(),
        )

        self.assertEqual(service.read_unread_messages(), ())
        self.assertEqual(driver.refresh_calls, 1)
        self.assertEqual(service.state, WhatsAppServiceState.IDLE)
        self.assertTrue(
            any(
                "Erro ao ler mensagem do WhatsApp" in message
                for message in service.pull_status_messages()
            )
        )

    def test_service_recovers_to_idle_when_send_hits_driver_error(self) -> None:
        class FailingMessageSender(FakeMessageSender):
            def send_message(
                self,
                content: str,
                customer_contact: str | None = None,
            ) -> bool:
                raise WebDriverException("DOM changed while sending")

        driver = FakeDriver({})
        service = WhatsAppService(
            driver=driver,
            selectors=FakeSelectors(),
            header_reader=FakeHeaderReader(),
            chat_list_reader=FakeChatListReader(opened=False, unread_count=1),
            message_extractor=FakeMessageExtractor(WhatsAppMessageSnapshot()),
            open_chat_reader=FakeOpenChatReader(),
            message_sender=FailingMessageSender(),
            message_state=WhatsAppMessageState(),
        )

        self.assertFalse(service.send_message("Resposta do bot", "Thiago"))
        self.assertEqual(driver.refresh_calls, 1)
        self.assertEqual(service.state, WhatsAppServiceState.IDLE)
        self.assertTrue(
            any(
                "Erro ao responder no WhatsApp" in message
                for message in service.pull_status_messages()
            )
        )


if __name__ == "__main__":
    unittest.main()
