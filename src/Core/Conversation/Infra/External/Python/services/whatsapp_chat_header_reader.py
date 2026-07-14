from selenium.common.exceptions import WebDriverException
from selenium.webdriver.remote.webdriver import WebDriver

from services.selenium_element_finder import SeleniumElementFinder
from services.whatsapp_locators import (
    CUSTOMER_CONTACT_FALLBACK_LOCATORS,
    OPEN_CHAT_LOCATORS,
    WHATSAPP_LOADED_LOCATORS,
)


class WhatsAppChatHeaderReader:
    def __init__(
        self,
        driver: WebDriver,
        element_finder: SeleniumElementFinder,
    ) -> None:
        self.driver = driver
        self.element_finder = element_finder

    def has_whatsapp_loaded(self) -> bool:
        try:
            loaded_elements = self.element_finder.find_visible_elements(
                WHATSAPP_LOADED_LOCATORS
            )
        except WebDriverException:
            return False

        return bool(loaded_elements)

    def has_open_chat(self) -> bool:
        try:
            open_chat_elements = self.element_finder.find_visible_elements(
                OPEN_CHAT_LOCATORS
            )
        except WebDriverException:
            return False

        return bool(open_chat_elements)

    def get_customer_phone(self) -> str:
        customer_phone = self.element_finder.wait_for_text(
            "customer_contact",
            CUSTOMER_CONTACT_FALLBACK_LOCATORS,
        )

        return customer_phone or "Contato não identificado"
