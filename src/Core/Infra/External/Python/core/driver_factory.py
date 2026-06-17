from collections.abc import Callable
from pathlib import Path
from typing import Any

from browser import BrowserFactory


class DriverFactory:
    def __init__(
        self,
        session_folder: str | Path,
        whatsapp_url: str,
        create_driver: Callable[[str | Path], Any] | None = None,
    ) -> None:
        self.session_folder = session_folder
        self.whatsapp_url = whatsapp_url
        self.create_driver = create_driver or BrowserFactory.create_chrome_driver

    def create(self) -> Any:
        driver = self.create_driver(self.session_folder)
        driver.get(self.whatsapp_url)

        return driver
