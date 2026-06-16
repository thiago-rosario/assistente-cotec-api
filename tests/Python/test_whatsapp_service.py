import sys
import unittest
from io import StringIO
from pathlib import Path
from tempfile import TemporaryDirectory
from unittest.mock import patch


PYTHON_APP_PATH = Path(__file__).resolve().parents[2] / "src/Core/Infra/External/Python"
sys.path.insert(0, str(PYTHON_APP_PATH))

from application.process_unread_message import ProcessUnreadMessageUseCase  # noqa: E402
from application.whatsapp_bot import WhatsAppBot  # noqa: E402
from domain.whatsapp_message import WhatsAppMessage  # noqa: E402
from services.php_bridge_message_formatter import PhpBridgeMessageFormatter  # noqa: E402
from services.php_bridge_command_listener import PhpBridgeCommandListener  # noqa: E402
from services.whatsapp_service import (  # noqa: E402
    CustomerMessageSnapshot,
    WhatsAppService,
)
from services.whatsapp_chat_reader import (  # noqa: E402
    WAIT_FOR_ACTIVITY_SCRIPT,
    WhatsAppChatReader,
)
from services.whatsapp_conversation_state import WhatsAppConversationState  # noqa: E402
from services.whatsapp_message_snapshot_reader import (  # noqa: E402
    MESSAGE_SNAPSHOT_SCRIPT,
)
from services.whatsapp_message_sender import WhatsAppMessageSender  # noqa: E402


class FakeSelectors:
    def get(self, selector_key: str) -> str:
        return selector_key


class FakeDriver:
    def __init__(self, message: object = "") -> None:
        self.message = message
        self.script_calls = []
        self.async_script_calls = []

    def execute_script(self, script: str, *args):
        if args:
            self.script_calls.append((script, args))

            return None

        return self.message

    def execute_async_script(self, script: str, *args):
        self.async_script_calls.append((script, args))

        return self.message


class FakeNavigator:
    def __init__(
        self,
        unread_results: tuple[bool, ...],
        unread_count: int = 1,
    ) -> None:
        self.unread_results = list(unread_results)
        self.last_opened_unread_count = unread_count
        self.open_unread_calls = 0

    def open_unread_chat(self) -> bool:
        self.open_unread_calls += 1

        return self.unread_results.pop(0) if self.unread_results else False


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


class FakeActivityGateway(FakeGateway):
    def __init__(self, messages: tuple[WhatsAppMessage, ...]) -> None:
        super().__init__(messages)
        self.activity_waits: list[float] = []

    def wait_for_activity(self, timeout_seconds: float) -> bool | None:
        self.activity_waits.append(timeout_seconds)

        return False


class FakeMessageBox:
    def __init__(self) -> None:
        self.sent_keys = []
        self.was_clicked = False

    def click(self) -> None:
        self.was_clicked = True

    def send_keys(self, value: str) -> None:
        self.sent_keys.append(value)


class SequencedReader:
    def __init__(
        self,
        *,
        snapshots: tuple[CustomerMessageSnapshot, ...],
        open_chat_results: tuple[bool, ...] = (),
    ) -> None:
        self.snapshots = list(snapshots)
        self.open_chat_results = list(open_chat_results)

    def has_open_chat(self) -> bool:
        return self.open_chat_results.pop(0) if self.open_chat_results else True

    def has_whatsapp_loaded(self) -> bool:
        return True

    def get_customer_phone(self) -> str:
        return "Thiago"

    def read_customer_message_snapshot(
        self,
    ) -> CustomerMessageSnapshot:
        return self.snapshots.pop(0) if self.snapshots else empty_snapshot()


class FakeReader:
    def __init__(
        self,
        *,
        snapshots: tuple[CustomerMessageSnapshot, ...],
        message_box: FakeMessageBox,
    ) -> None:
        self.snapshots = list(snapshots)
        self.last_snapshot = snapshots[-1] if snapshots else empty_snapshot()
        self.message_box = message_box

    def has_open_chat(self) -> bool:
        return True

    def get_customer_phone(self) -> str:
        return "Thiago"

    def read_customer_message_snapshot(self) -> CustomerMessageSnapshot:
        if self.snapshots:
            self.last_snapshot = self.snapshots.pop(0)

        return self.last_snapshot

    def build_locator(self, selector_key: str) -> tuple[str, str]:
        return "class name", selector_key

    def wait_for_elements(
        self,
        locators: tuple[tuple[str, str], ...],
        timeout: int = 5,
    ) -> list[FakeMessageBox]:
        return [self.message_box]


def empty_snapshot() -> CustomerMessageSnapshot:
    return CustomerMessageSnapshot(
        incoming_messages=(),
        outgoing_messages=(),
        incoming_after_last_outgoing=(),
        conversation_count=0,
    )


def snapshot(
    incoming_messages: tuple[str, ...],
    incoming_after_last_outgoing: tuple[str, ...] = (),
    conversation_count: int | None = None,
    outgoing_messages: tuple[str, ...] = (),
    incoming_message_keys: tuple[str, ...] = (),
    incoming_after_last_outgoing_message_keys: tuple[str, ...] = (),
    incoming_message_timestamps: tuple[str, ...] = (),
    incoming_after_last_outgoing_message_timestamps: tuple[str, ...] = (),
) -> CustomerMessageSnapshot:
    return CustomerMessageSnapshot(
        incoming_messages=incoming_messages,
        outgoing_messages=outgoing_messages,
        incoming_after_last_outgoing=incoming_after_last_outgoing,
        conversation_count=conversation_count
        if conversation_count is not None
        else len(incoming_messages) + len(outgoing_messages),
        incoming_message_keys=incoming_message_keys,
        incoming_after_last_outgoing_message_keys=(
            incoming_after_last_outgoing_message_keys
        ),
        incoming_message_timestamps=incoming_message_timestamps,
        incoming_after_last_outgoing_message_timestamps=(
            incoming_after_last_outgoing_message_timestamps
        ),
    )


def service_with_sequences(
    *,
    snapshots: tuple[CustomerMessageSnapshot, ...],
    unread_results: tuple[bool, ...],
    open_chat_results: tuple[bool, ...] = (),
    unread_count: int = 1,
    conversation_state: WhatsAppConversationState | None = None,
    needs_initial_baseline: bool = True,
) -> WhatsAppService:
    reader = SequencedReader(
        snapshots=snapshots,
        open_chat_results=open_chat_results,
    )
    navigator = FakeNavigator(unread_results, unread_count)

    service = WhatsAppService(
        driver=FakeDriver(),
        selectors=FakeSelectors(),
        reader=reader,
        navigator=navigator,
        conversation_state=conversation_state,
    )
    service._needs_initial_baseline = needs_initial_baseline

    return service


class WhatsAppServiceTest(unittest.TestCase):
    def test_reads_last_customer_message_from_dom_before_fallback(self) -> None:
        reader = WhatsAppChatReader(
            driver=FakeDriver("030.2647.2023.0170476-39"),
            selectors=FakeSelectors(),
        )

        self.assertEqual(
            reader.get_last_customer_message(),
            "030.2647.2023.0170476-39",
        )

    def test_reads_recent_customer_messages_from_dom_before_fallback(self) -> None:
        reader = WhatsAppChatReader(
            driver=FakeDriver(["020.4487.2021.0009714-69", "ANDARAÍ"]),
            selectors=FakeSelectors(),
        )

        self.assertEqual(
            reader.get_recent_customer_messages(limit=2),
            ("020.4487.2021.0009714-69", "ANDARAÍ"),
        )

    def test_reads_recent_customer_messages_after_last_outgoing_message(self) -> None:
        reader = WhatsAppChatReader(
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
            reader.get_recent_customer_messages(),
            ("ANDARAÍ",),
        )

    def test_ignores_outgoing_messages_when_reading_recent_customer_messages(
        self,
    ) -> None:
        reader = WhatsAppChatReader(
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
            reader.read_recent_customer_messages_from_dom(limit=1),
            [],
        )

    def test_snapshot_keeps_outgoing_messages_out_of_customer_input(self) -> None:
        reader = WhatsAppChatReader(
            driver=FakeDriver(
                {
                    "incomingTexts": ("Barra",),
                    "outgoingTexts": (
                        "Olá! Eu sou o assistente da COTEC.",
                        "Registro do Caderno Técnico",
                    ),
                    "allConversationTexts": (
                        "Olá! Eu sou o assistente da COTEC.",
                        "Barra",
                        "Registro do Caderno Técnico",
                    ),
                    "incomingAfterLastOutgoingTexts": ("Barra",),
                    "conversationCount": 3,
                }
            ),
            selectors=FakeSelectors(),
        )

        snapshot = reader.read_customer_message_snapshot()

        self.assertEqual(snapshot.incoming_messages, ("Barra",))
        self.assertEqual(
            snapshot.outgoing_messages,
            (
                "Olá! Eu sou o assistente da COTEC.",
                "Registro do Caderno Técnico",
            ),
        )
        self.assertNotIn(
            "Registro do Caderno Técnico",
            snapshot.incoming_messages,
        )

    def test_snapshot_script_reads_plain_text_message_nodes(self) -> None:
        self.assertIn("[data-pre-plain-text]", MESSAGE_SNAPSHOT_SCRIPT)
        self.assertIn("messageDataId.startsWith('true_')", MESSAGE_SNAPSHOT_SCRIPT)
        self.assertIn("messageDataId.startsWith('false_')", MESSAGE_SNAPSHOT_SCRIPT)
        self.assertIn("senderMatchesChatTitle", MESSAGE_SNAPSHOT_SCRIPT)
        self.assertIn("incomingMessageKeys", MESSAGE_SNAPSHOT_SCRIPT)
        self.assertIn("incomingMessageTimestamps", MESSAGE_SNAPSHOT_SCRIPT)
        self.assertNotIn("hasPlainText", MESSAGE_SNAPSHOT_SCRIPT)
        self.assertNotIn("|| hasPlainText", MESSAGE_SNAPSHOT_SCRIPT)

    def test_does_not_emit_unread_message_when_text_content_is_empty(self) -> None:
        service = service_with_sequences(
            snapshots=(empty_snapshot(),),
            unread_results=(True,),
            open_chat_results=(False,),
        )

        self.assertEqual(service.read_unread_messages(), ())

    def test_reads_new_customer_message_when_chat_is_already_open(self) -> None:
        service = service_with_sequences(
            snapshots=(
                snapshot(("020.4487.2021.0009714-69",)),
                snapshot(("020.4487.2021.0009714-69",)),
                snapshot(("020.4487.2021.0009714-69", "ANDARAÍ")),
            ),
            unread_results=(False, False, False),
        )

        first_messages = service.read_unread_messages()
        repeated_messages = service.read_unread_messages()
        open_chat_messages = service.read_unread_messages()

        self.assertEqual(first_messages, ())
        self.assertEqual(repeated_messages, ())
        self.assertEqual(len(open_chat_messages), 1)
        self.assertEqual(open_chat_messages[0].customer_contact, "Thiago")
        self.assertEqual(open_chat_messages[0].content, "ANDARAÍ")

    def test_prioritizes_new_message_in_open_chat_before_unread_badges(self) -> None:
        service = service_with_sequences(
            snapshots=(
                snapshot(
                    ("020.4487.2021.0009714-69", "ANDARAÍ"),
                    ("ANDARAÍ",),
                ),
            ),
            unread_results=(True,),
        )
        service._seen_incoming_messages_by_contact["Thiago"] = (
            "020.4487.2021.0009714-69",
        )

        messages = service.read_unread_messages()

        self.assertEqual(
            tuple(message.content for message in messages),
            ("ANDARAÍ",),
        )
        self.assertEqual(service.navigator.open_unread_calls, 0)

    def test_baselines_open_chat_message_after_last_outgoing_on_first_scan(self) -> None:
        service = service_with_sequences(
            snapshots=(
                snapshot(
                    ("020.4487.2021.0009714-69", "ANDARAÍ"),
                    ("ANDARAÍ",),
                ),
            ),
            unread_results=(False,),
        )

        messages = service.read_unread_messages()

        self.assertEqual(messages, ())

    def test_baselines_all_open_chat_messages_after_last_outgoing_on_first_scan(
        self,
    ) -> None:
        service = service_with_sequences(
            snapshots=(
                snapshot(
                    ("mensagem antiga", "outra antiga", "mensagem nova"),
                    ("outra antiga", "mensagem nova"),
                ),
            ),
            unread_results=(False,),
        )

        messages = service.read_unread_messages()

        self.assertEqual(messages, ())

    def test_baselines_latest_open_chat_message_without_history(self) -> None:
        service = service_with_sequences(
            snapshots=(
                snapshot(("mensagem antiga", "mensagem nova")),
            ),
            unread_results=(False,),
        )

        messages = service.read_unread_messages()

        self.assertEqual(messages, ())

    def test_reads_badged_chat_after_open_chat_has_no_new_messages(self) -> None:
        service = service_with_sequences(
            snapshots=(
                snapshot(("old",)),
                snapshot(("old", "new"), ("new",)),
            ),
            unread_results=(True,),
        )
        service._seen_incoming_messages_by_contact["Thiago"] = ("old",)

        messages = service.read_unread_messages()

        self.assertEqual(tuple(message.content for message in messages), ("new",))
        self.assertEqual(service.navigator.open_unread_calls, 1)

    def test_reads_only_unread_count_from_badged_chat(self) -> None:
        service = service_with_sequences(
            snapshots=(
                snapshot(("old 1", "old 2", "new 1", "new 2")),
            ),
            unread_results=(True,),
            open_chat_results=(False,),
            unread_count=2,
        )

        messages = service.read_unread_messages()

        self.assertEqual(
            tuple(message.content for message in messages),
            ("new 1", "new 2"),
        )

    def test_emits_external_id_and_timestamp_from_snapshot_metadata(self) -> None:
        conversation_state = WhatsAppConversationState()
        service = service_with_sequences(
            snapshots=(
                snapshot(
                    ("Ipira",),
                    incoming_message_keys=("false_abc123",),
                    incoming_message_timestamps=("15/06/2026, 15:01:37",),
                ),
            ),
            unread_results=(True,),
            open_chat_results=(False,),
            conversation_state=conversation_state,
        )

        messages = service.read_unread_messages()

        self.assertEqual(len(messages), 1)
        self.assertEqual(
            messages[0].external_id,
            conversation_state.message_key("Thiago", "Ipira", "false_abc123"),
        )
        self.assertEqual(messages[0].received_at, "15/06/2026, 15:01:37")

    def test_persists_processed_message_keys_between_service_restarts(self) -> None:
        with TemporaryDirectory() as temporary_directory:
            state_path = Path(temporary_directory) / "whatsapp_state.json"
            first_state = WhatsAppConversationState(state_path)
            first_service = service_with_sequences(
                snapshots=(
                    snapshot(
                        ("Ipira",),
                        incoming_message_keys=("false_msg_1",),
                    ),
                ),
                unread_results=(True,),
                open_chat_results=(False,),
                conversation_state=first_state,
            )

            first_messages = first_service.read_unread_messages()

            self.assertEqual(len(first_messages), 1)

            second_state = WhatsAppConversationState(state_path)
            second_service = service_with_sequences(
                snapshots=(
                    snapshot(
                        ("Ipira",),
                        incoming_message_keys=("false_msg_1",),
                    ),
                ),
                unread_results=(True,),
                open_chat_results=(False,),
                conversation_state=second_state,
            )

            repeated_messages = second_service.read_unread_messages()

            self.assertEqual(repeated_messages, ())
            self.assertTrue(
                any(
                    "já ter sido processada" in message
                    for message in second_service.pull_status_messages()
                )
            )

    def test_persists_sent_bot_replies_between_state_reloads(self) -> None:
        with TemporaryDirectory() as temporary_directory:
            state_path = Path(temporary_directory) / "whatsapp_state.json"
            bot_reply = "Olá! Eu sou o assistente da COTEC."
            conversation_state = WhatsAppConversationState(state_path)
            conversation_state.remember("Thiago", ("Oi",))
            conversation_state.remember_sent_message("Thiago", bot_reply)

            reloaded_state = WhatsAppConversationState(state_path)
            messages = reloaded_state.resolve_open_chat_new_messages(
                customer_contact="Thiago",
                current_incoming_messages=("Oi", bot_reply),
                incoming_after_last_outgoing=(bot_reply,),
            )

            self.assertEqual(messages, ())
            self.assertEqual(reloaded_state.get_seen("Thiago"), ("Oi",))

    def test_diff_without_overlap_returns_only_detected_tail_growth(self) -> None:
        messages = WhatsAppConversationState().diff_new_messages(
            ("old 1", "old 2"),
            ("visible old", "visible newer", "new"),
        )

        self.assertEqual(messages, ("new",))

    def test_ignores_sent_bot_reply_when_whatsapp_reports_it_as_incoming(
        self,
    ) -> None:
        conversation_state = WhatsAppConversationState()
        conversation_state.remember("Thiago", ("Barra",))
        conversation_state.remember_sent_message(
            "Thiago",
            "Não encontrei registros para essa consulta.",
        )

        messages = conversation_state.resolve_open_chat_new_messages(
            customer_contact="Thiago",
            current_incoming_messages=(
                "Barra",
                "Não encontrei registros para essa consulta.",
            ),
            incoming_after_last_outgoing=(
                "Não encontrei registros para essa consulta.",
            ),
        )

        self.assertEqual(messages, ())
        self.assertEqual(
            conversation_state.get_seen("Thiago"),
            ("Barra",),
        )

    def test_keeps_sent_reply_filtered_after_it_was_seen_in_the_snapshot(
        self,
    ) -> None:
        conversation_state = WhatsAppConversationState()
        bot_reply = "Olá! Eu sou o assistente da COTEC."

        conversation_state.remember("Thiago", ("Oi",))
        conversation_state.remember_sent_message("Thiago", bot_reply)
        conversation_state.remember("Thiago", ("Oi", bot_reply))

        messages = conversation_state.resolve_open_chat_new_messages(
            customer_contact="Thiago",
            current_incoming_messages=("Oi", bot_reply),
            incoming_after_last_outgoing=(bot_reply,),
        )

        self.assertEqual(messages, ())
        self.assertEqual(conversation_state.get_seen("Thiago"), ("Oi",))

    def test_filters_recent_sent_reply_when_contact_name_changes(self) -> None:
        conversation_state = WhatsAppConversationState()
        bot_reply = "Recebi sua mensagem, mas o serviço está no limite."

        conversation_state.remember("Thiago Rosario Souza", ("Oi",))
        conversation_state.remember_sent_message("Contato não identificado", bot_reply)

        messages = conversation_state.resolve_open_chat_new_messages(
            customer_contact="Thiago Rosario Souza",
            current_incoming_messages=("Oi", bot_reply),
            incoming_after_last_outgoing=(bot_reply,),
        )

        self.assertEqual(messages, ())
        self.assertEqual(
            conversation_state.get_seen("Thiago Rosario Souza"),
            ("Oi",),
        )

    def test_keeps_real_customer_message_after_misclassified_bot_reply(
        self,
    ) -> None:
        conversation_state = WhatsAppConversationState()
        conversation_state.remember("Thiago", ("Barra",))
        conversation_state.remember_sent_message(
            "Thiago",
            "Não encontrei registros para essa consulta.",
        )

        messages = conversation_state.resolve_open_chat_new_messages(
            customer_contact="Thiago",
            current_incoming_messages=(
                "Barra",
                "Não encontrei registros para essa consulta.",
                "Antas",
            ),
            incoming_after_last_outgoing=(
                "Não encontrei registros para essa consulta.",
                "Antas",
            ),
        )

        self.assertEqual(messages, ("Antas",))
        self.assertEqual(
            conversation_state.get_seen("Thiago"),
            ("Barra", "Antas"),
        )

    def test_send_message_keeps_bot_response_out_of_customer_history(self) -> None:
        driver = FakeDriver()
        message_box = FakeMessageBox()
        conversation_state = WhatsAppConversationState()
        reader = FakeReader(
            snapshots=(
                snapshot(("cliente",), conversation_count=3),
                snapshot(("cliente",), conversation_count=4),
                snapshot(("cliente",), conversation_count=4),
            ),
            message_box=message_box,
        )
        sender = WhatsAppMessageSender(
            driver=driver,
            selectors=FakeSelectors(),
            reader=reader,
            conversation_state=conversation_state,
        )
        service = WhatsAppService(
            driver=driver,
            selectors=FakeSelectors(),
            reader=reader,
            sender=sender,
            conversation_state=conversation_state,
        )

        sent = service.send_message("Resposta do bot", customer_contact="Thiago")

        self.assertTrue(sent)
        self.assertEqual(
            service._seen_incoming_messages_by_contact["Thiago"],
            ("cliente",),
        )
        self.assertNotIn(
            "Resposta do bot",
            service._seen_incoming_messages_by_contact["Thiago"],
        )

    def test_send_message_filters_bot_response_from_customer_history(self) -> None:
        driver = FakeDriver()
        message_box = FakeMessageBox()
        conversation_state = WhatsAppConversationState()
        reader = FakeReader(
            snapshots=(
                snapshot(("cliente",), conversation_count=1),
                snapshot(("cliente", "Resposta do bot"), conversation_count=2),
                snapshot(("cliente", "Resposta do bot"), conversation_count=2),
            ),
            message_box=message_box,
        )
        sender = WhatsAppMessageSender(
            driver=driver,
            selectors=FakeSelectors(),
            reader=reader,
            conversation_state=conversation_state,
        )
        service = WhatsAppService(
            driver=driver,
            selectors=FakeSelectors(),
            reader=reader,
            sender=sender,
            conversation_state=conversation_state,
        )

        sent = service.send_message("Resposta do bot", customer_contact="Thiago")

        self.assertTrue(sent)
        self.assertEqual(
            service._seen_incoming_messages_by_contact["Thiago"],
            ("cliente",),
        )

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

        processed_message = bot._process_next_message()

        bridge_events = tuple(line for line in output if line.startswith("{"))

        self.assertTrue(processed_message)
        self.assertEqual(len(bridge_events), 2)
        self.assertIn('"content": "020.4487.2021.0009714-69"', bridge_events[0])
        self.assertIn('"content": "ANDARAÍ"', bridge_events[1])
        self.assertTrue(
            any("Payload enviado ao PHP/Laravel" in line for line in output)
        )

    def test_bot_uses_active_interval_after_unread_message(self) -> None:
        bot = WhatsAppBot(
            ProcessUnreadMessageUseCase(
                FakeGateway((WhatsAppMessage("Thiago", "ANDARAÍ"),))
            ),
            interval_seconds=0.5,
            output=lambda _: None,
            message_formatter=PhpBridgeMessageFormatter(),
            active_interval_seconds=0.1,
        )

        with patch("application.whatsapp_bot.time.sleep") as sleep:
            bot.run(max_cycles=1)

        sleep.assert_called_once_with(0.1)

    def test_bot_uses_idle_interval_when_no_unread_message(self) -> None:
        bot = WhatsAppBot(
            ProcessUnreadMessageUseCase(FakeGateway(())),
            interval_seconds=0.5,
            output=lambda _: None,
            message_formatter=PhpBridgeMessageFormatter(),
            active_interval_seconds=0.1,
        )

        with patch("application.whatsapp_bot.time.sleep") as sleep:
            bot.run(max_cycles=1)

        sleep.assert_called_once_with(0.5)

    def test_types_non_bmp_characters_with_javascript_insert_text(self) -> None:
        driver = FakeDriver()
        message_box = FakeMessageBox()
        reader = FakeReader(
            snapshots=(),
            message_box=message_box,
        )
        sender = WhatsAppMessageSender(
            driver=driver,
            selectors=FakeSelectors(),
            reader=reader,
            conversation_state=WhatsAppConversationState(),
        )

        sender._type_message(message_box, "Ola \U0001F600")

        self.assertTrue(message_box.was_clicked)
        self.assertEqual(message_box.sent_keys, ["\ue007"])
        self.assertEqual(len(driver.script_calls), 1)
        self.assertEqual(driver.script_calls[0][1], (message_box, "Ola \U0001F600"))


if __name__ == "__main__":
    unittest.main()
