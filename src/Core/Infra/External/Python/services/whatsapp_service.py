from selenium.common.exceptions import WebDriverException
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.remote.webelement import WebElement
from selenium.webdriver.remote.webdriver import WebDriver
from selenium.webdriver.support.ui import WebDriverWait

from domain.whatsapp_message import WhatsAppMessage
from domain.whatsapp_selectors import WhatsAppSelectors


Locator = tuple[str, str]
LocatorGroup = tuple[Locator, ...]

UNREAD_LABEL_CONDITIONS = (
    "contains(translate(@aria-label, "
    "'ABCDEFGHIJKLMNOPQRSTUVWXYZÁÀÂÃÉÊÍÓÔÕÚÇ', "
    "'abcdefghijklmnopqrstuvwxyzáàâãéêíóôõúç'), "
    "'mensagem não lida')",
    "contains(translate(@aria-label, "
    "'ABCDEFGHIJKLMNOPQRSTUVWXYZÁÀÂÃÉÊÍÓÔÕÚÇ', "
    "'abcdefghijklmnopqrstuvwxyzáàâãéêíóôõúç'), "
    "'mensagens não lidas')",
    "contains(translate(@aria-label, "
    "'ABCDEFGHIJKLMNOPQRSTUVWXYZ', "
    "'abcdefghijklmnopqrstuvwxyz'), "
    "'unread message')",
    "contains(translate(@aria-label, "
    "'ABCDEFGHIJKLMNOPQRSTUVWXYZ', "
    "'abcdefghijklmnopqrstuvwxyz'), "
    "'unread messages')",
)

UNREAD_LABEL_XPATH = " or ".join(UNREAD_LABEL_CONDITIONS)


class WhatsAppService:
    CUSTOMER_CONTACT_FALLBACK_LOCATORS = (
        (
            By.XPATH,
            "//header//span[@title and normalize-space(@title) != '']",
        ),
        (
            By.XPATH,
            "//header//*[@role='button']//span[@dir='auto' and normalize-space()]",
        ),
        (
            By.XPATH,
            "//header//span[@dir='auto' and normalize-space()]",
        ),
    )
    CUSTOMER_MESSAGE_FALLBACK_LOCATORS = (
        (
            By.CSS_SELECTOR,
            "div.message-in span.selectable-text",
        ),
        (
            By.CSS_SELECTOR,
            "div.message-in div.copyable-text span",
        ),
        (
            By.XPATH,
            "//div[contains(@class, 'message-in')]"
            "//*[contains(@class, 'selectable-text') and normalize-space()]",
        ),
        (
            By.XPATH,
            "//div[contains(@class, 'message-in')]"
            "//*[@data-pre-plain-text and normalize-space()]",
        ),
    )
    UNREAD_CHAT_FALLBACK_LOCATORS = (
        (
            By.XPATH,
            f"//div[@role='listitem'][.//*[{UNREAD_LABEL_XPATH}]]",
        ),
        (
            By.XPATH,
            f"//*[@role='row'][.//*[{UNREAD_LABEL_XPATH}]]",
        ),
        (
            By.XPATH,
            "//*[@data-icon='unread-count']"
            "/ancestor::*[@role='listitem' or @role='row'][1]",
        ),
    )
    MESSAGE_BOX_FALLBACK_LOCATORS = (
        (
            By.XPATH,
            "//footer//div[@contenteditable='true' and @role='textbox']",
        ),
        (
            By.XPATH,
            "//footer//div[@contenteditable='true']",
        ),
        (
            By.XPATH,
            "//div[@role='textbox' and @contenteditable='true']",
        ),
    )

    def __init__(self, driver: WebDriver, selectors: WhatsAppSelectors) -> None:
        self.driver = driver
        self.selectors = selectors

    def _build_locator(self, selector_key: str) -> Locator:
        selector = self.selectors.get(selector_key)

        if selector.startswith(("/", "(")):
            return By.XPATH, selector

        if selector.startswith(("#", ".")) or "[" in selector:
            return By.CSS_SELECTOR, selector

        return By.CLASS_NAME, selector

    def _find_elements(self, locators: LocatorGroup) -> list[WebElement]:
        elements = []

        for locator in locators:
            try:
                elements.extend(self.driver.find_elements(*locator))
            except WebDriverException:
                continue

        return [element for element in elements if element.is_displayed()]

    def _read_element_text(self, element: WebElement) -> str:
        text_values = (
            element.get_attribute("title"),
            element.get_attribute("innerText"),
            element.text,
        )

        for text in text_values:
            if text and text.strip():
                return text.strip()

        return ""

    def _wait_for_elements(
        self,
        locators: LocatorGroup,
        timeout: int = 5,
    ) -> list[WebElement]:
        return WebDriverWait(self.driver, timeout).until(
            lambda _: self._find_elements(locators)
        )

    def _wait_for_text(
        self,
        selector_key: str,
        fallback_locators: LocatorGroup,
    ) -> str:
        locator_groups = (
            (self._build_locator(selector_key),),
            fallback_locators,
        )

        for locators in locator_groups:
            try:
                elements = self._wait_for_elements(locators)
            except WebDriverException:
                continue

            for element in reversed(elements):
                text = self._read_element_text(element)

                if text:
                    return text

        return ""

    def _find_chat_container(self, badge: WebElement) -> WebElement | None:
        return self.driver.execute_script(
            """
            const badge = arguments[0];
            return badge.closest('[role="listitem"], [role="row"]') || badge;
            """,
            badge,
        )

    def _find_unread_chats_by_selector(self) -> list[WebElement]:
        try:
            notification_badges = self.driver.find_elements(
                *self._build_locator("notification_badge"),
            )
        except WebDriverException:
            return []

        return [
            chat
            for badge in notification_badges
            if badge.is_displayed()
            if (chat := self._find_chat_container(badge))
        ]

    def _click_chat(self, chat: WebElement) -> None:
        self.driver.execute_script(
            "arguments[0].scrollIntoView({block: 'center'});",
            chat,
        )

        try:
            chat.click()
        except WebDriverException:
            action = ActionChains(self.driver)
            action.move_to_element(chat)
            action.click()
            action.perform()

    def open_unread_chat(self) -> bool:
        unread_chats = (
            self._find_unread_chats_by_selector()
            or self._find_elements(self.UNREAD_CHAT_FALLBACK_LOCATORS)
        )

        if not unread_chats:
            return False

        self._click_chat(unread_chats[-1])

        try:
            WebDriverWait(self.driver, 5).until(lambda _: self.has_open_chat())
        except WebDriverException:
            return False

        return True

    def has_whatsapp_loaded(self) -> bool:
        loaded_elements = self.driver.find_elements(
            By.XPATH,
            "//*[@id='pane-side']"
            " | //*[@aria-label='Lista de conversas']"
            " | //*[@aria-label='Chat list']"
            " | //*[@role='grid']",
        )

        return any(element.is_displayed() for element in loaded_elements)

    def has_open_chat(self) -> bool:
        chats = self.driver.find_elements(
            By.XPATH,
            "//footer//div[@contenteditable='true']"
            " | //div[@role='textbox' and @contenteditable='true']",
        )

        return any(chat.is_displayed() for chat in chats)

    def get_customer_phone(self) -> str:
        customer_phone = self._wait_for_text(
            "customer_contact",
            self.CUSTOMER_CONTACT_FALLBACK_LOCATORS,
        )

        return customer_phone or "Contato não identificado"

    def get_last_customer_message(self) -> str:
        customer_message = self._wait_for_text(
            "customer_message",
            self.CUSTOMER_MESSAGE_FALLBACK_LOCATORS,
        )

        return customer_message or "Mensagem sem texto identificável"

    def read_last_unread_message(self) -> WhatsAppMessage | None:
        has_unread_chat = self.open_unread_chat()

        if not has_unread_chat:
            return None

        return WhatsAppMessage(
            customer_contact=self.get_customer_phone(),
            content=self.get_last_customer_message(),
        )

    def send_message(
        self,
        content: str,
        customer_contact: str | None = None,
    ) -> bool:
        if not content.strip() or not self.has_open_chat():
            return False

        message_box = self._find_message_box()

        if message_box is None:
            return False

        self._type_message(message_box, content)

        return True

    def _find_message_box(self) -> WebElement | None:
        locator_groups = (
            (self._build_locator("message_box"),),
            self.MESSAGE_BOX_FALLBACK_LOCATORS,
        )

        for locators in locator_groups:
            try:
                elements = self._wait_for_elements(locators, timeout=5)
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
                message_box.send_keys(line)

        message_box.send_keys(Keys.ENTER)
