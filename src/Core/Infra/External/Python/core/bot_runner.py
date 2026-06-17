import logging
import time
from collections.abc import Callable
from typing import Any


class BotRunner:
    def __init__(
        self,
        driver_factory: Any,
        recovery_manager: Any,
        message_processor: Any,
        interval_seconds: int,
        health_checker: Any | None = None,
        sleep: Callable[[float], None] = time.sleep,
        logger: logging.Logger | None = None,
    ) -> None:
        self.driver_factory = driver_factory
        self.recovery_manager = recovery_manager
        self.message_processor = message_processor
        self.interval_seconds = interval_seconds
        self.health_checker = health_checker
        self.sleep = sleep
        self.logger = logger or logging.getLogger(__name__)
        self.driver: Any | None = None

    def start(self) -> None:
        self.driver = self.driver_factory.create()
        self.message_processor.attach_driver(self.driver)
        self.logger.info("Bot iniciado e aguardando mensagens no WhatsApp Web.")

    def run(self) -> None:
        if self.driver is None:
            self.start()

        while True:
            self.run_once()
            self.sleep(self.interval_seconds)

    def run_once(self) -> None:
        if self.driver is None:
            self.start()

        try:
            result = self.message_processor.process()
            self.recovery_manager.reset_failures()
            self._check_health(getattr(result, "whatsapp_loaded", None))
        except Exception as error:
            current_driver = self.driver
            self.driver = self.recovery_manager.handle(error, current_driver)

            if self.driver is not current_driver:
                self.message_processor.attach_driver(self.driver)

            self._check_health()

    def _check_health(self, is_loaded: bool | None = None) -> None:
        if self.health_checker is None:
            return

        self.health_checker.check(is_loaded)
