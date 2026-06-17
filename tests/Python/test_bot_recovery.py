import logging
import sys
import unittest
from pathlib import Path
from tempfile import TemporaryDirectory
from types import SimpleNamespace


PYTHON_APP_PATH = Path(__file__).resolve().parents[2] / "src/Core/Infra/External/Python"
sys.path.insert(0, str(PYTHON_APP_PATH))

from core.bot_runner import BotRunner  # noqa: E402
from core.recovery_manager import BotRecoveryError, RecoveryManager  # noqa: E402
from core.whatsapp_health_checker import WhatsAppHealthChecker  # noqa: E402
from services.whatsapp_message_extractor import ExtractedWhatsAppMessage  # noqa: E402
from services.whatsapp_message_state import WhatsAppMessageState  # noqa: E402


class FakeDriver:
    def __init__(self, name: str = "driver") -> None:
        self.name = name
        self.refresh_calls = 0
        self.quit_calls = 0

    def get(self, url: str) -> None:
        return None

    def refresh(self) -> None:
        self.refresh_calls += 1

    def quit(self) -> None:
        self.quit_calls += 1


class FakeDriverFactory:
    def __init__(self) -> None:
        self.created = 0

    def create(self) -> FakeDriver:
        self.created += 1

        return FakeDriver(f"driver-{self.created}")


class FailingDriverFactory:
    def create(self) -> FakeDriver:
        raise RuntimeError("chrome unavailable")


class SequencedProcessor:
    def __init__(self, outcomes: tuple[Exception | object | None, ...]) -> None:
        self.outcomes = list(outcomes)
        self.attachments: list[FakeDriver] = []
        self.process_calls = 0

    def attach_driver(self, driver: FakeDriver) -> None:
        self.attachments.append(driver)

    def process(self) -> object | None:
        self.process_calls += 1
        outcome = self.outcomes.pop(0) if self.outcomes else None

        if outcome is not None:
            if isinstance(outcome, Exception):
                raise outcome

            return outcome

        return None


class FakeHealthChecker:
    def __init__(self) -> None:
        self.checks: list[bool | None] = []

    def check(self, is_loaded: bool | None = None) -> bool:
        self.checks.append(is_loaded)

        return True


class BotRecoveryTest(unittest.TestCase):
    def test_isolated_error_is_logged_and_next_success_resets_failure_count(self) -> None:
        driver_factory = FakeDriverFactory()
        recovery_manager = RecoveryManager(
            driver_factory,
            isolated_failure_sleep_seconds=0,
            refresh_wait_seconds=0,
            restart_wait_seconds=0,
        )
        processor = SequencedProcessor((RuntimeError("temporary failure"), None))
        runner = BotRunner(
            driver_factory=driver_factory,
            recovery_manager=recovery_manager,
            message_processor=processor,
            interval_seconds=0,
            sleep=lambda _: None,
        )

        with self.assertLogs("core.recovery_manager", level=logging.ERROR) as logs:
            runner.run_once()

        runner.run_once()

        self.assertIn("Falhas consecutivas: 1/5", "\n".join(logs.output))
        self.assertEqual(recovery_manager.consecutive_failures, 0)
        self.assertEqual(driver_factory.created, 1)
        self.assertEqual(processor.process_calls, 2)
        self.assertEqual(len(processor.attachments), 1)

    def test_success_reuses_known_health_status_without_extra_lookup(self) -> None:
        driver_factory = FakeDriverFactory()
        health_checker = FakeHealthChecker()
        processor = SequencedProcessor(
            (SimpleNamespace(whatsapp_loaded=False),)
        )
        runner = BotRunner(
            driver_factory=driver_factory,
            recovery_manager=RecoveryManager(
                driver_factory,
                isolated_failure_sleep_seconds=0,
                refresh_wait_seconds=0,
                restart_wait_seconds=0,
            ),
            message_processor=processor,
            health_checker=health_checker,
            interval_seconds=0,
            sleep=lambda _: None,
        )

        runner.run_once()

        self.assertEqual(health_checker.checks, [False])

    def test_refreshes_whatsapp_web_once_before_restart_threshold(self) -> None:
        driver_factory = FakeDriverFactory()
        driver = driver_factory.create()
        recovery_manager = RecoveryManager(
            driver_factory,
            isolated_failure_sleep_seconds=0,
            refresh_wait_seconds=0,
            restart_wait_seconds=0,
        )

        with self.assertLogs("core.recovery_manager", level=logging.ERROR):
            for failure in range(4):
                returned_driver = recovery_manager.handle(
                    RuntimeError(f"failure {failure}"),
                    driver,
                )

        self.assertIs(returned_driver, driver)
        self.assertEqual(driver.refresh_calls, 1)
        self.assertEqual(driver.quit_calls, 0)
        self.assertEqual(recovery_manager.consecutive_failures, 4)

    def test_five_consecutive_errors_restart_driver(self) -> None:
        driver_factory = FakeDriverFactory()
        driver = driver_factory.create()
        recovery_manager = RecoveryManager(
            driver_factory,
            isolated_failure_sleep_seconds=0,
            refresh_wait_seconds=0,
            restart_wait_seconds=0,
        )

        with self.assertLogs("core.recovery_manager", level=logging.ERROR):
            for failure in range(5):
                returned_driver = recovery_manager.handle(
                    RuntimeError(f"failure {failure}"),
                    driver,
                )

        self.assertIsNot(returned_driver, driver)
        self.assertEqual(driver.quit_calls, 1)
        self.assertEqual(driver_factory.created, 2)
        self.assertEqual(recovery_manager.consecutive_failures, 0)

    def test_restart_failure_raises_so_process_supervisor_can_restart_bot(self) -> None:
        driver = FakeDriver()
        recovery_manager = RecoveryManager(
            FailingDriverFactory(),
            isolated_failure_sleep_seconds=0,
            refresh_wait_seconds=0,
            restart_wait_seconds=0,
        )

        with self.assertLogs("core.recovery_manager", level=logging.CRITICAL):
            with self.assertRaises(BotRecoveryError):
                for failure in range(5):
                    recovery_manager.handle(RuntimeError(f"failure {failure}"), driver)

    def test_disconnected_session_logs_reconnection_instruction(self) -> None:
        health_checker = WhatsAppHealthChecker(lambda: False)

        with self.assertLogs("core.whatsapp_health_checker", level=logging.ERROR) as logs:
            self.assertFalse(health_checker.check())

        output = "\n".join(logs.output)

        self.assertIn("Sessão do WhatsApp Web desconectada", output)
        self.assertIn("QR Code", output)

    def test_processed_message_state_survives_driver_and_process_restart(self) -> None:
        with TemporaryDirectory() as temp_directory:
            state_path = Path(temp_directory) / "whatsapp_message_state.json"
            message = ExtractedWhatsAppMessage(
                direction="incoming",
                text="mensagem antiga",
                key="false_old_message",
            )
            first_state = WhatsAppMessageState(state_path)
            first_state.remember_seen("Thiago", (message,))
            reloaded_state = WhatsAppMessageState(state_path)

            self.assertTrue(reloaded_state.is_processed("Thiago", message))
            self.assertEqual(
                reloaded_state.filter_new_customer_messages("Thiago", (message,)),
                (),
            )


if __name__ == "__main__":
    unittest.main()
