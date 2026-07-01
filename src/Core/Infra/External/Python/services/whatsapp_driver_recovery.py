from collections.abc import Callable

from selenium.common.exceptions import WebDriverException
from selenium.webdriver.remote.webdriver import WebDriver


class WhatsAppDriverRecovery:
    def __init__(
        self,
        driver: WebDriver,
        record_status: Callable[[str], None],
    ) -> None:
        self.driver = driver
        self.record_status = record_status

    def recover(self, action: str, error: WebDriverException) -> None:
        self.record_status(f"Erro ao {action}: {error}")

        refresh = getattr(self.driver, "refresh", None)

        if not callable(refresh):
            return

        try:
            refresh()
        except WebDriverException as refresh_error:
            self.record_status(f"Erro ao recarregar WhatsApp Web: {refresh_error}")
