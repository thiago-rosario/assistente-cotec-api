import logging
import time
from typing import Any

logger = logging.getLogger(__name__)


class BotRecoveryError(RuntimeError):
    pass


class RecoveryManager:
    def __init__(
        self,
        driver_factory: Any,
        refresh_threshold: int = 3,
        restart_threshold: int = 5,
        isolated_failure_sleep_seconds: float = 2,
        refresh_wait_seconds: float = 5,
        restart_wait_seconds: float = 3,
    ) -> None:
        self.driver_factory = driver_factory
        self.refresh_threshold = refresh_threshold
        self.restart_threshold = restart_threshold
        self.isolated_failure_sleep_seconds = isolated_failure_sleep_seconds
        self.refresh_wait_seconds = refresh_wait_seconds
        self.restart_wait_seconds = restart_wait_seconds
        self.consecutive_failures = 0

    def reset_failures(self) -> None:
        if self.consecutive_failures:
            logger.info(
                "Loop do bot recuperado após %s falha(s) consecutiva(s).",
                self.consecutive_failures,
            )

        self.consecutive_failures = 0

    def handle(self, error: Exception, driver: Any) -> Any:
        self.consecutive_failures += 1

        logger.error(
            "Erro no loop do bot. Falhas consecutivas: %s/%s. Erro: %s",
            self.consecutive_failures,
            self.restart_threshold,
            error,
            exc_info=error,
        )

        if self.consecutive_failures >= self.restart_threshold:
            return self.restart_browser(driver)

        if self.consecutive_failures == self.refresh_threshold:
            self.refresh_browser(driver)

            return driver

        logger.info("Falha recuperável no bot; mantendo driver atual.")
        time.sleep(self.isolated_failure_sleep_seconds)

        return driver

    def refresh_browser(self, driver: Any) -> None:
        logger.warning(
            "Executando refresh do WhatsApp Web após %s falhas consecutivas.",
            self.consecutive_failures,
        )

        try:
            driver.refresh()
            time.sleep(self.refresh_wait_seconds)
        except Exception as error:
            logger.error(
                "Falha ao tentar atualizar o navegador. Erro: %s",
                error,
                exc_info=error,
            )

    def restart_browser(self, driver: Any) -> Any:
        logger.critical(
            "Reiniciando navegador após %s falhas consecutivas.",
            self.consecutive_failures,
        )

        try:
            driver.quit()
        except Exception as error:
            logger.error(
                "Falha ao fechar driver antigo. O bot tentará criar um novo driver. "
                "Erro: %s",
                error,
                exc_info=error,
            )

        time.sleep(self.restart_wait_seconds)

        try:
            new_driver = self.driver_factory.create()
        except Exception as error:
            logger.critical(
                "Falha ao recriar o navegador. Encerrando o processo para permitir "
                "reinício pelo sistema operacional, Docker ou Supervisor. Erro: %s",
                error,
                exc_info=error,
            )
            raise BotRecoveryError("Falha ao recriar o navegador.") from error

        logger.info("Navegador recriado com sucesso.")
        self.consecutive_failures = 0

        return new_driver
