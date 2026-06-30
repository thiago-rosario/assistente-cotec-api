import threading
from collections.abc import Callable
from typing import TypeVar

from selenium.common.exceptions import WebDriverException
from selenium.webdriver.remote.webdriver import WebDriver

from domain.whatsapp_message import WhatsAppMessage
from services.whatsapp_driver_recovery import WhatsAppDriverRecovery
from services.whatsapp_processing_tracker import WhatsAppProcessingTracker
from services.whatsapp_service_state import WhatsAppServiceState


T = TypeVar("T")


class WhatsAppDriverSession:
    def __init__(
        self,
        driver: WebDriver,
        record_status: Callable[[str], None],
        processing_tracker: WhatsAppProcessingTracker | None = None,
        recovery: WhatsAppDriverRecovery | None = None,
    ) -> None:
        self._lock = threading.RLock()
        self._state = WhatsAppServiceState.IDLE
        self._processing_tracker = processing_tracker or WhatsAppProcessingTracker()
        self._record_status = record_status
        self._recovery = recovery or WhatsAppDriverRecovery(driver, record_status)

    @property
    def state(self) -> WhatsAppServiceState:
        return self._state

    def is_busy(self) -> bool:
        with self._lock:
            return (
                self._state != WhatsAppServiceState.IDLE
                or self._processing_tracker.has_pending_messages()
            )

    def run(self, action: Callable[[], T]) -> T:
        with self._lock:
            return action()

    def run_read(
        self,
        action: Callable[[], tuple[WhatsAppMessage, ...]],
    ) -> tuple[WhatsAppMessage, ...]:
        with self._lock:
            if self._processing_tracker.has_pending_messages():
                self._record_status(
                    "Mensagem ignorada temporariamente: bot aguardando "
                    "processamento da resposta atual."
                )

                return ()

            self._transition_to(WhatsAppServiceState.READING)

            try:
                return action()
            except WebDriverException as error:
                self._recover_from_driver_error("ler mensagem do WhatsApp", error)

                return ()
            finally:
                if self._state == WhatsAppServiceState.READING:
                    self._transition_after_driver_action()

    def run_send(self, action: Callable[[], bool]) -> bool:
        with self._lock:
            self._transition_to(WhatsAppServiceState.SENDING)

            try:
                return action()
            except WebDriverException as error:
                self._recover_from_driver_error("responder no WhatsApp", error)

                return False
            finally:
                if self._state == WhatsAppServiceState.SENDING:
                    self._transition_after_driver_action()

    def begin_processing_messages(
        self,
        messages: tuple[WhatsAppMessage, ...],
    ) -> None:
        with self._lock:
            self._processing_tracker.begin(messages)

            if (
                self._processing_tracker.has_pending_messages()
                and self._state == WhatsAppServiceState.IDLE
            ):
                self._transition_to(WhatsAppServiceState.PROCESSING)

    def finish_processing_message(
        self,
        customer_contact: str | None,
        external_id: str | None,
    ) -> None:
        with self._lock:
            self._processing_tracker.finish(customer_contact, external_id)

            if (
                not self._processing_tracker.has_pending_messages()
                and self._state == WhatsAppServiceState.PROCESSING
            ):
                self._transition_to(WhatsAppServiceState.IDLE)

    def _transition_to(self, state: WhatsAppServiceState) -> None:
        self._state = state

    def _transition_after_driver_action(self) -> None:
        if self._processing_tracker.has_pending_messages():
            self._transition_to(WhatsAppServiceState.PROCESSING)

            return

        self._transition_to(WhatsAppServiceState.IDLE)

    def _recover_from_driver_error(
        self,
        action: str,
        error: WebDriverException,
    ) -> None:
        self._transition_to(WhatsAppServiceState.RECOVERING)
        self._recovery.recover(action, error)
        self._transition_after_driver_action()
