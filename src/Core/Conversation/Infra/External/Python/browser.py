from pathlib import Path

from selenium import webdriver
from selenium.webdriver.chrome.options import Options


class BrowserFactory:
    @staticmethod
    def create_chrome_driver(session_folder: str | Path) -> webdriver.Chrome:
        session_path = Path(session_folder).expanduser()

        if not session_path.is_absolute():
            session_path = Path.cwd() / session_path

        session_path.mkdir(parents=True, exist_ok=True)

        chrome_options = Options()
        chrome_options.add_argument(f"--user-data-dir={session_path}")

        return webdriver.Chrome(options=chrome_options)
