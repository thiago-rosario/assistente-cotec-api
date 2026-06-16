from selenium.common.exceptions import WebDriverException
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.remote.webdriver import WebDriver
from selenium.webdriver.remote.webelement import WebElement

from services.selenium_element_finder import SeleniumElementFinder
from services.whatsapp_chat_header_reader import WhatsAppChatHeaderReader
from services.whatsapp_locators import MESSAGE_BOX_FALLBACK_LOCATORS
from services.whatsapp_message_state import WhatsAppMessageState


class WhatsAppMessageSender:
    def __init__(
        self,
        driver: WebDriver,
        header_reader: WhatsAppChatHeaderReader,
        element_finder: SeleniumElementFinder,
        message_state: WhatsAppMessageState,
    ) -> None:
        self.driver = driver
        self.header_reader = header_reader
        self.element_finder = element_finder
        self.message_state = message_state

    def send_message(
        self,
        content: str,
        customer_contact: str | None = None,
    ) -> bool:
        content = content.strip()

        if not content or not self.header_reader.has_open_chat():
            return False

        message_box = self._find_message_box()

        if message_box is None:
            return False

        self._type_message(message_box, content)
        self.message_state.remember_sent_message(content)

        return True

    def _find_message_box(self) -> WebElement | None:
        locator_groups = (
            (self.element_finder.build_locator("message_box"),),
            MESSAGE_BOX_FALLBACK_LOCATORS,
        )

        for locators in locator_groups:
            try:
                elements = self.element_finder.wait_for_elements(
                    locators,
                    timeout=5,
                )
            except WebDriverException:
                continue

            if elements:
                return elements[-1]

        return None

    def _type_message(self, message_box: WebElement, content: str) -> None:
        message_box.click()

        lines = content.splitlines() or [content]

        for index, line in enumerate(lines):
            if index > 0:
                ActionChains(self.driver).key_down(Keys.SHIFT).send_keys(
                    Keys.ENTER
                ).key_up(Keys.SHIFT).perform()

            if line:
                self._type_line(message_box, line)

        message_box.send_keys(Keys.ENTER)

    def _type_line(self, message_box: WebElement, line: str) -> None:
        if self._has_non_bmp_character(line):
            self.driver.execute_script(
                """
                const messageBox = arguments[0];
                const text = arguments[1];

                messageBox.focus();
                document.execCommand('insertText', false, text);
                """,
                message_box,
                line,
            )

            return

        message_box.send_keys(line)

    def _has_non_bmp_character(self, value: str) -> bool:
        return any(ord(character) > 0xFFFF for character in value)
