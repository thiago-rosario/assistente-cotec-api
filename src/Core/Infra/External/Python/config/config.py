import os
from pathlib import Path

from dotenv import load_dotenv

EXTERNAL_DIR = Path(__file__).resolve().parents[1]
PROJECT_ROOT = Path(__file__).resolve().parents[5]

if PROJECT_ROOT.name == "src":
    PROJECT_ROOT = PROJECT_ROOT.parent

load_dotenv(PROJECT_ROOT / ".env")


class Config:
    EDITACODIGO_API_KEY = (
        os.getenv("EDITACODIGO_API_KEY")
        or os.getenv("EDITACODIGO_TOKEN")
        or os.getenv("TOKEN")
    )
    WHATSAPP_URL = os.getenv(
        "WHATSAPP_URL",
        os.getenv("SITE", "https://web.whatsapp.com/"),
    )
    DOWNLOAD_FOLDER = os.getenv(
        "DOWNLOAD_ARQUIVOS",
        str(EXTERNAL_DIR / "pasta" / "downloads"),
    )
    SESSION_FOLDER = os.getenv(
        "WHATSAPP_SESSION_FOLDER",
        os.getenv("SESSAO_PASTA", str(EXTERNAL_DIR / "pasta" / "sessao")),
    )
    WEBHOOK_URL = os.getenv("WEBHOOK", os.getenv("SERVIDOR", "localhost"))
    USER = os.getenv("USUARIO", "editacodigo_user")
    PORT = int(os.getenv("PORTA", "5000"))
    API_BASE_URL = os.getenv(
        "EDITACODIGO_API_URL",
        os.getenv("API", "https://editacodigo.com.br/api/"),
    )
    REQUEST_TIMEOUT = 10
    BOT_INTERVAL_SECONDS = 3

    @classmethod
    def get_editacodigo_api_key(cls) -> str:
        if not cls.EDITACODIGO_API_KEY:
            raise ValueError(
                "Defina a variável de ambiente EDITACODIGO_API_KEY. "
                "Também são aceitos os nomes EDITACODIGO_TOKEN ou TOKEN."
            )

        return cls.EDITACODIGO_API_KEY
