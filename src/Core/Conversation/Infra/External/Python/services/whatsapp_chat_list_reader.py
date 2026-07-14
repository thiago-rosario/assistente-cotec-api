from selenium.common.exceptions import WebDriverException
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.remote.webdriver import WebDriver
from selenium.webdriver.remote.webelement import WebElement
from selenium.webdriver.support.ui import WebDriverWait

from services.selenium_element_finder import SeleniumElementFinder
from services.whatsapp_chat_header_reader import WhatsAppChatHeaderReader
from services.whatsapp_locators import UNREAD_CHAT_FALLBACK_LOCATORS


class WhatsAppChatListReader:
    def __init__(
        self,
        driver: WebDriver,
        element_finder: SeleniumElementFinder,
        header_reader: WhatsAppChatHeaderReader,
    ) -> None:
        self.driver = driver
        self.element_finder = element_finder
        self.header_reader = header_reader
        self.last_opened_unread_count = 1

    def open_unread_chat(self) -> bool:
        unread_chats = (
            self._find_unread_chats_by_selector()
            or self.element_finder.find_visible_elements(UNREAD_CHAT_FALLBACK_LOCATORS)
        )

        if not unread_chats:
            return False

        unread_chat = unread_chats[-1]
        self.last_opened_unread_count = self._unread_count_from_chat(unread_chat)
        self._click_chat(unread_chat)

        try:
            WebDriverWait(self.driver, 5).until(
                lambda _: self.header_reader.has_open_chat()
            )
        except WebDriverException:
            return False

        return True

    def _find_unread_chats_by_selector(self) -> list[WebElement]:
        try:
            notification_badges = self.driver.find_elements(
                *self.element_finder.build_locator("notification_badge"),
            )
        except WebDriverException:
            return []

        return [
            chat
            for badge in notification_badges
            if badge.is_displayed()
            if (chat := self._find_chat_container(badge))
        ]

    def _find_chat_container(self, badge: WebElement) -> WebElement | None:
        try:
            return self.driver.execute_script(
                """
                const badge = arguments[0];
                return badge.closest('[role="listitem"], [role="row"]') || badge;
                """,
                badge,
            )
        except WebDriverException:
            return None

    def _unread_count_from_chat(self, chat: WebElement) -> int:
        try:
            unread_count = self.driver.execute_script(
                """
                const chat = arguments[0];
                const unreadLabelPattern = /mensagens? n[aã]o lidas?|unread messages?/i;
                const elements = [chat, ...chat.querySelectorAll('[aria-label]')];

                for (const element of elements) {
                    const label = element.getAttribute?.('aria-label') || '';

                    if (! unreadLabelPattern.test(label)) {
                        continue;
                    }

                    const countMatch = label.match(/\\d+/);

                    return countMatch ? Number(countMatch[0]) : 1;
                }

                const badge = chat.querySelector('[data-icon="unread-count"]');
                const text = badge?.closest('span, div')?.textContent
                    || badge?.textContent
                    || '';
                const countMatch = text.match(/\\d+/);

                return countMatch ? Number(countMatch[0]) : 1;
                """,
                chat,
            )
        except WebDriverException:
            return 1

        if not isinstance(unread_count, int) or unread_count < 1:
            return 1

        return unread_count

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
