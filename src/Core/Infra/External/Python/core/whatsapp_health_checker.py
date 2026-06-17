import logging
from collections.abc import Callable


class WhatsAppHealthChecker:
    def __init__(
        self,
        has_whatsapp_loaded: Callable[[], bool],
        logger: logging.Logger | None = None,
    ) -> None:
        self.has_whatsapp_loaded = has_whatsapp_loaded
        self.logger = logger or logging.getLogger(__name__)
        self._was_disconnected = False

    def check(self, is_loaded: bool | None = None) -> bool:
        if is_loaded is None:
            is_loaded = self.has_whatsapp_loaded()

        if not is_loaded:
            if not self._was_disconnected:
                self.logger.error(
                    "Sessão do WhatsApp Web desconectada ou não carregada. "
                    "Reconecte a sessão no navegador, possivelmente lendo o QR Code."
                )

            self._was_disconnected = True

            return False

        if self._was_disconnected:
            self.logger.info("Sessão do WhatsApp Web carregada novamente.")

        self._was_disconnected = False

        return True
