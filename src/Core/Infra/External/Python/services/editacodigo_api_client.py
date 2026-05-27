import base64
from dataclasses import dataclass
from typing import Any

import requests

from config.config import Config


@dataclass(frozen=True)
class RemoteModuleCredentials:
    file_url: str
    username: str
    password: str


class EditaCodigoApiClient:
    REQUIRED_CREDENTIAL_KEYS = ("url_base64", "username_base64", "password_base64")

    def __init__(self) -> None:
        self.headers = {
            "User-Agent": (
                "Mozilla/5.0 (Windows NT 6.3; WOW64) "
                "AppleWebKit/537.36 (KHTML, like Gecko) "
                "Chrome/59.0.3071.115 Safari/537.36"
            )
        }

    def fetch_remote_module_credentials(self) -> RemoteModuleCredentials:
        response_data = self._fetch_credentials_payload()

        return self._build_remote_module_credentials(response_data)

    def download_remote_module(self, credentials: RemoteModuleCredentials) -> bytes:
        module_response = requests.get(
            credentials.file_url,
            auth=(credentials.username, credentials.password),
            headers=self.headers,
            timeout=Config.REQUEST_TIMEOUT,
        )
        module_response.raise_for_status()

        return module_response.content

    def _fetch_credentials_payload(self) -> dict[str, Any]:
        credentials_response = requests.post(
            Config.API_BASE_URL,
            data={"token": Config.get_editacodigo_api_key()},
            headers=self.headers,
            timeout=Config.REQUEST_TIMEOUT,
        )
        credentials_response.raise_for_status()

        response_data = credentials_response.json()

        if response_data.get("status") != "success":
            message = response_data.get(
                "message",
                "resposta sem mensagem de erro.",
            )

            raise ValueError(f"API do Edita Código recusou o token: {message}")

        return response_data

    def _build_remote_module_credentials(
        self,
        response_data: dict[str, Any],
    ) -> RemoteModuleCredentials:
        missing_keys = [
            key for key in self.REQUIRED_CREDENTIAL_KEYS if not response_data.get(key)
        ]

        if missing_keys:
            raise ValueError(
                "Resposta inválida da API do Edita Código: "
                f"campos ausentes: {', '.join(missing_keys)}."
            )

        return RemoteModuleCredentials(
            file_url=self._decode_api_value(response_data["url_base64"]),
            username=self._decode_api_value(response_data["username_base64"]),
            password=self._decode_api_value(response_data["password_base64"]),
        )

    def _decode_api_value(self, value: str) -> str:
        encoded_value = value[9:]
        padding = len(encoded_value) % 4

        if padding:
            encoded_value += "=" * (4 - padding)

        return base64.b64decode(encoded_value).decode("utf-8")
