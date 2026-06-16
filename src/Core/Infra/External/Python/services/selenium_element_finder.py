from selenium.common.exceptions import WebDriverException
from selenium.webdriver.common.by import By
from selenium.webdriver.remote.webdriver import WebDriver
from selenium.webdriver.remote.webelement import WebElement
from selenium.webdriver.support.ui import WebDriverWait

from domain.whatsapp_selectors import WhatsAppSelectors
from services.whatsapp_locators import Locator, LocatorGroup


class SeleniumElementFinder:
    def __init__(
        self,
        driver: WebDriver,
        selectors: WhatsAppSelectors,
    ) -> None:
        self.driver = driver
        self.selectors = selectors

    def build_locator(self, selector_key: str) -> Locator:
        selector = self.selectors.get(selector_key)

        if selector.startswith(("/", "(")):
            return By.XPATH, selector

        if selector.startswith(("#", ".")) or "[" in selector:
            return By.CSS_SELECTOR, selector

        return By.CLASS_NAME, selector

    def find_visible_elements(self, locators: LocatorGroup) -> list[WebElement]:
        elements: list[WebElement] = []

        for locator in locators:
            try:
                elements.extend(self.driver.find_elements(*locator))
            except WebDriverException:
                continue

        return [element for element in elements if element.is_displayed()]

    def read_element_text(self, element: WebElement) -> str:
        text_values = (
            element.get_attribute("title"),
            element.get_attribute("innerText"),
            element.text,
        )

        for text in text_values:
            if text and text.strip():
                return text.strip()

        return ""

    def wait_for_elements(
        self,
        locators: LocatorGroup,
        timeout: int = 5,
    ) -> list[WebElement]:
        return WebDriverWait(self.driver, timeout).until(
            lambda _: self.find_visible_elements(locators)
        )

    def wait_for_text(
        self,
        selector_key: str,
        fallback_locators: LocatorGroup,
    ) -> str:
        locator_groups = (
            (self.build_locator(selector_key),),
            fallback_locators,
        )

        for locators in locator_groups:
            try:
                elements = self.wait_for_elements(locators)
            except WebDriverException:
                continue

            for element in reversed(elements):
                text = self.read_element_text(element)

                if text:
                    return text

        return ""
