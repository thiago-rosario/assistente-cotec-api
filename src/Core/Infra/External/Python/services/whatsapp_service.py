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
            By.CSS_SELECTOR,
            "div.message-in [data-pre-plain-text]",
        ),
        (
            By.CSS_SELECTOR,
            "[data-pre-plain-text] span.selectable-text",
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
        (
            By.XPATH,
            "//*[@data-pre-plain-text]"
            "//*[contains(@class, 'selectable-text') and normalize-space()]",
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
        self._last_opened_unread_count = 1
        self._last_processed_open_chat_signature: str | None = None

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

    def _read_last_customer_message_from_dom(self) -> str:
        messages = self._read_recent_customer_messages_from_dom(limit=1)

        return messages[-1] if messages else ""

    def _read_recent_customer_messages_from_dom(
        self,
        limit: int | None = None,
        only_after_last_outgoing: bool = False,
    ) -> list[str]:
        try:
            messages = self.driver.execute_script(
                """
                const onlyAfterLastOutgoing = arguments[0];

                const normalize = (value) => String(value || '')
                    .replace(/\u200e|\u200f/g, '')
                    .replace(/\\s+/g, ' ')
                    .trim();

                const visibleText = (element) => normalize(
                    element?.innerText || element?.textContent || ''
                );

                const chatTitle = normalize(
                    document.querySelector('header span[title]')?.getAttribute('title')
                    || document.querySelector('header span[dir="auto"]')?.textContent
                    || ''
                );

                const messageDataId = (element) => {
                    const dataElement = element.matches('[data-id]')
                        ? element
                        : element.closest('[data-id]')
                            || element.querySelector('[data-id]');

                    return dataElement?.getAttribute('data-id') || '';
                };

                const messageSender = (element) => {
                    const plainTextElement = element.matches('[data-pre-plain-text]')
                        ? element
                        : element.querySelector('[data-pre-plain-text]');
                    const plainText = plainTextElement
                        ?.getAttribute('data-pre-plain-text') || '';
                    const senderMatch = plainText.match(/\\]\\s*(.*?):\\s*$/);

                    return normalize(senderMatch?.[1] || '');
                };

                const senderMatchesChatTitle = (sender) => {
                    return sender && chatTitle && sender === chatTitle;
                };

                const messageDirection = (element) => {
                    const dataId = messageDataId(element);

                    if (dataId.startsWith('true_')) {
                        return 'outgoing';
                    }

                    if (dataId.startsWith('false_')) {
                        return 'incoming';
                    }

                    const sender = messageSender(element);

                    if (senderMatchesChatTitle(sender)) {
                        return 'incoming';
                    }

                    if (sender && chatTitle) {
                        return 'outgoing';
                    }

                    const className = String(element.className || '');

                    if (
                        className.includes('message-out')
                        || element.matches('[class*="message-out"]')
                        || element.closest('[class*="message-out"]') !== null
                    ) {
                        return 'outgoing';
                    }

                    if (
                        className.includes('message-in')
                        || element.matches('[class*="message-in"]')
                        || element.closest('[class*="message-in"]') !== null
                        || element.hasAttribute('data-pre-plain-text')
                    ) {
                        return 'incoming';
                    }

                    return '';
                };

                const messageText = (container) => {
                    const selectorGroups = [
                        'span.selectable-text',
                        'div.copyable-text span',
                        '[data-pre-plain-text] span',
                    ];

                    for (const selector of selectorGroups) {
                        const textNodes = [
                        ...(container.matches(selector) ? [container] : []),
                        ...container.querySelectorAll(selector),
                    ].filter((element, index, list) => {
                            return list.findIndex((candidate) => {
                                return candidate === element
                                    || candidate.contains(element)
                                    || element.contains(candidate);
                            }) === index;
                        });

                        const lines = textNodes
                            .map(visibleText)
                            .flatMap((text) => text.split('\\n'))
                            .map(normalize)
                            .filter(Boolean)
                            .filter((text) => ! /^\\d{1,2}:\\d{2}$/.test(text));

                        const uniqueLines = [...new Set(lines)];

                        if (uniqueLines.length) {
                            return uniqueLines.join('\\n').trim();
                        }
                    }

                    const fallbackLines = visibleText(container)
                        .split('\\n')
                        .map(normalize)
                        .filter(Boolean)
                        .filter((text) => ! /^\\d{1,2}:\\d{2}$/.test(text));

                    return [...new Set(fallbackLines)].join('\\n').trim();
                };

                const messageContainers = [
                    ...document.querySelectorAll(
                        'div.message-in, div.message-out, '
                        + '[class*="message-in"], [class*="message-out"], '
                        + '[data-id], [data-pre-plain-text]'
                    ),
                ]
                    .map((element) => {
                        return element.closest(
                            'div.message-in, div.message-out, '
                            + '[class*="message-in"], [class*="message-out"], '
                            + '[data-id]'
                        ) || element.closest('[data-pre-plain-text]') || element;
                    })
                    .filter((element, index, list) => list.indexOf(element) === index);

                const rows = messageContainers
                .map((element) => {
                    const direction = messageDirection(element);

                    return {
                        incoming: direction === 'incoming',
                        outgoing: direction === 'outgoing',
                        text: messageText(element),
                    };
                })
                .filter((row) => row.incoming || row.outgoing);

                let lastOutgoingIndex = -1;

                rows.forEach((row, index) => {
                    if (row.outgoing) {
                        lastOutgoingIndex = index;
                    }
                });

                const unreadIncomingTexts = rows
                    .filter((row, index) => row.incoming
                        && row.text
                        && index > lastOutgoingIndex)
                    .map((row) => row.text);

                if (unreadIncomingTexts.length) {
                    return unreadIncomingTexts;
                }

                if (onlyAfterLastOutgoing) {
                    return [];
                }

                return rows
                    .filter((row) => row.incoming && row.text)
                    .map((row) => row.text);
                """,
                only_after_last_outgoing,
            )
        except WebDriverException:
            return []

        if isinstance(messages, list):
            text_messages = [
                str(message).strip()
                for message in messages
                if str(message).strip()
            ]
        elif messages:
            text_messages = [str(messages).strip()]
        else:
            text_messages = []

        if limit is not None and limit > 0:
            return text_messages[-limit:]

        return text_messages

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
                const text = badge?.closest('span, div')?.textContent || badge?.textContent || '';
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

    def read_open_chat_new_messages(self) -> tuple[WhatsAppMessage, ...]:
        if not self.has_open_chat():
            return ()

        customer_contact = self.get_customer_phone()

        messages = self._read_recent_customer_messages_from_dom(
            limit=1,
            only_after_last_outgoing=True,
        )

        if not messages:
            return ()

        message = messages[-1]
        signature = f"{customer_contact}|{message}"

        if signature == self._last_processed_open_chat_signature:
            return ()

        self._last_processed_open_chat_signature = signature

        return (
            WhatsAppMessage(
                customer_contact=customer_contact,
                content=message,
            ),
        )

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

        unread_chat = unread_chats[-1]
        self._last_opened_unread_count = self._unread_count_from_chat(unread_chat)
        self._click_chat(unread_chat)

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
        customer_message = self._read_last_customer_message_from_dom()

        if customer_message:
            return customer_message

        return self._wait_for_text(
            "customer_message",
            self.CUSTOMER_MESSAGE_FALLBACK_LOCATORS,
        )

    def get_recent_customer_messages(
        self,
        limit: int | None = None,
    ) -> tuple[str, ...]:
        customer_messages = self._read_recent_customer_messages_from_dom(limit)

        if customer_messages:
            return tuple(customer_messages)

        fallback_message = self._wait_for_text(
            "customer_message",
            self.CUSTOMER_MESSAGE_FALLBACK_LOCATORS,
        )

        return (fallback_message,) if fallback_message else ()

    def read_unread_messages(self) -> tuple[WhatsAppMessage, ...]:
        has_unread_chat = self.open_unread_chat()

        if not has_unread_chat:
            return self.read_open_chat_new_messages()

        customer_contact = self.get_customer_phone()
        messages = self.get_recent_customer_messages(
            limit=self._last_opened_unread_count,
        )

        if not messages:
            return ()

        return tuple(
            WhatsAppMessage(
                customer_contact=customer_contact,
                content=message,
            )
            for message in messages
        )

    def read_last_unread_message(self) -> WhatsAppMessage | None:
        messages = self.read_unread_messages()

        return messages[-1] if messages else None

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
