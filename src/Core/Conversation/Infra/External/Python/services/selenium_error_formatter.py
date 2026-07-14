from selenium.common.exceptions import (
    NoSuchElementException,
    TimeoutException,
    WebDriverException,
)


class SeleniumErrorFormatter:
    def __call__(self, error: Exception) -> str:
        if isinstance(error, NoSuchElementException):
            return "Elemento não encontrado no WhatsApp Web."

        if isinstance(error, TimeoutException):
            return "Tempo limite ao tentar localizar elemento."

        if isinstance(error, WebDriverException):
            return f"Erro no navegador: {error}"

        return f"Erro inesperado: {error}"
