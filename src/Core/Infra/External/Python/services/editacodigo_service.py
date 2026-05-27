from types import ModuleType

from config.config import Config
from domain.whatsapp_selectors import WhatsAppSelectors
from services.editacodigo_api_client import EditaCodigoApiClient
from services.remote_module_loader import RemoteModuleLoader
from services.whatsapp_selector_mapper import WhatsAppSelectorMapper


class EditaCodigoService:
    def __init__(
        self,
        api_client: EditaCodigoApiClient | None = None,
        module_loader: RemoteModuleLoader | None = None,
        selector_mapper: WhatsAppSelectorMapper | None = None,
    ) -> None:
        self.api_client = api_client or EditaCodigoApiClient()
        self.module_loader = module_loader or RemoteModuleLoader()
        self.selector_mapper = selector_mapper or WhatsAppSelectorMapper()

    def get_whatsapp_selectors(self) -> WhatsAppSelectors:
        editacodigo = self._load_remote_module()
        selectors = editacodigo.obter_classes_whatsapp(
            Config.get_editacodigo_api_key()
        )

        if not isinstance(selectors, dict):
            raise ValueError(
                "Resposta inválida da API do Edita Código: "
                "seletores não retornados em formato de dicionário."
            )

        return self.selector_mapper.map(selectors)

    def _load_remote_module(self) -> ModuleType:
        credentials = self.api_client.fetch_remote_module_credentials()
        module_content = self.api_client.download_remote_module(credentials)

        return self.module_loader.load("editacodigo", module_content)
