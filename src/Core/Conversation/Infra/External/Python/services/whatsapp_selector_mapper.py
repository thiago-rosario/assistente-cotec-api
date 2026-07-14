from dataclasses import fields
from typing import Any

from domain.whatsapp_selectors import WhatsAppSelectors


class WhatsAppSelectorMapper:
    SELECTOR_ALIASES = {
        "notification_badge": "css_bolinha_notificacao",
        "customer_contact": "class_tel_cliente",
        "message_box": "class_caixa_mensagem_cod",
        "customer_message": "class_msg_cliente",
        "message_box_alternative": "botao_enviar_texto",
        "search_box": "caixa_pesquisa",
    }

    def __init__(self) -> None:
        self.selector_keys = tuple(field.name for field in fields(WhatsAppSelectors))

    def map(self, selectors: dict[str, Any]) -> WhatsAppSelectors:
        normalized_selectors = {
            key: str(selector_value).strip()
            for key in self.selector_keys
            if (selector_value := self._get_selector_value(selectors, key)) is not None
        }

        self._validate_selectors(normalized_selectors)

        return WhatsAppSelectors(**normalized_selectors)

    def _get_selector_value(self, selectors: dict[str, Any], key: str) -> Any:
        api_key = self.SELECTOR_ALIASES[key]

        return selectors.get(key, selectors.get(api_key))

    def _validate_selectors(self, selectors: dict[str, str]) -> None:
        missing_selectors = [
            key for key in self.selector_keys if key not in selectors
        ]

        if missing_selectors:
            raise ValueError(
                "Resposta inválida da API do Edita Código: "
                f"seletores ausentes: {', '.join(missing_selectors)}."
            )

        empty_selectors = [
            key for key, selector in selectors.items() if not selector
        ]

        if empty_selectors:
            raise ValueError(
                "Resposta inválida da API do Edita Código: "
                f"seletores vazios para {', '.join(empty_selectors)}."
            )
