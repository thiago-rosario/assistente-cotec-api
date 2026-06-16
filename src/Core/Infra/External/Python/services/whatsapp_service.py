from selenium.webdriver.remote.webdriver import WebDriver
from selenium.webdriver.remote.webelement import WebElement

from domain.whatsapp_message import WhatsAppMessage
from domain.whatsapp_selectors import WhatsAppSelectors
from services.selenium_element_finder import SeleniumElementFinder
from services.whatsapp_chat_header_reader import WhatsAppChatHeaderReader
from services.whatsapp_chat_list_reader import WhatsAppChatListReader
from services.whatsapp_locators import (
    CUSTOMER_CONTACT_FALLBACK_LOCATORS,
    CUSTOMER_MESSAGE_FALLBACK_LOCATORS,
    MESSAGE_BOX_FALLBACK_LOCATORS,
    UNREAD_CHAT_FALLBACK_LOCATORS,
    Locator,
    LocatorGroup,
)
from services.whatsapp_message_extractor import (
    ExtractedWhatsAppMessage,
    WhatsAppMessageExtractor,
    WhatsAppMessageSnapshot,
)
from services.whatsapp_message_sender import WhatsAppMessageSender
from services.whatsapp_message_state import WhatsAppMessageState
from services.whatsapp_open_chat_reader import WhatsAppOpenChatReader


class WhatsAppService:
    CUSTOMER_CONTACT_FALLBACK_LOCATORS = CUSTOMER_CONTACT_FALLBACK_LOCATORS
    CUSTOMER_MESSAGE_FALLBACK_LOCATORS = CUSTOMER_MESSAGE_FALLBACK_LOCATORS
    UNREAD_CHAT_FALLBACK_LOCATORS = UNREAD_CHAT_FALLBACK_LOCATORS
    MESSAGE_BOX_FALLBACK_LOCATORS = MESSAGE_BOX_FALLBACK_LOCATORS
    MESSAGE_SCAN_LIMIT = 50

    def __init__(
        self,
        driver: WebDriver,
        selectors: WhatsAppSelectors,
        element_finder: SeleniumElementFinder | None = None,
        header_reader: WhatsAppChatHeaderReader | None = None,
        chat_list_reader: WhatsAppChatListReader | None = None,
        message_extractor: WhatsAppMessageExtractor | None = None,
        open_chat_reader: WhatsAppOpenChatReader | None = None,
        message_sender: WhatsAppMessageSender | None = None,
        message_state: WhatsAppMessageState | None = None,
    ) -> None:
        self.driver = driver
        self.selectors = selectors
        self.message_state = message_state or WhatsAppMessageState()
        self.element_finder = element_finder or SeleniumElementFinder(
            driver,
            selectors,
        )
        self.header_reader = header_reader or WhatsAppChatHeaderReader(
            driver,
            self.element_finder,
        )
        self.message_extractor = message_extractor or WhatsAppMessageExtractor(driver)
        self.chat_list_reader = chat_list_reader or WhatsAppChatListReader(
            driver,
            self.element_finder,
            self.header_reader,
        )
        self.open_chat_reader = open_chat_reader or WhatsAppOpenChatReader(
            self.header_reader,
            self.message_extractor,
            self.message_state,
            self.element_finder,
        )
        self.message_sender = message_sender or WhatsAppMessageSender(
            driver,
            self.header_reader,
            self.element_finder,
            self.message_state,
        )
        self._status_messages: list[str] = []
        self._seen_incoming_messages_by_contact = self.message_state.seen_by_contact

    def read_unread_messages(self) -> tuple[WhatsAppMessage, ...]:
        open_chat_messages = self.open_chat_reader.read_new_customer_messages()

        if open_chat_messages:
            return open_chat_messages

        if not self.chat_list_reader.open_unread_chat():
            return ()

        customer_contact = self.get_customer_phone()
        snapshot = self.message_extractor.extract(
            message_limit=self._unread_message_scan_limit(),
        )
        candidates = self._candidate_messages_from_unread_chat(snapshot)
        new_messages = self._filter_new_customer_messages(
            customer_contact,
            candidates,
        )

        return self._to_whatsapp_messages(customer_contact, new_messages)

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

        customer_contact = customer_contact or self.get_customer_phone()
        snapshot = self.message_extractor.extract(
            message_limit=self.MESSAGE_SCAN_LIMIT,
        )

        if snapshot.incoming_messages:
            self.message_state.remember_seen(
                customer_contact,
                snapshot.incoming_messages,
            )

        return self.message_sender.send_message(content, customer_contact)

    def pull_status_messages(self) -> tuple[str, ...]:
        messages = tuple(self._status_messages)
        self._status_messages.clear()

        return messages

    def open_unread_chat(self) -> bool:
        return self.chat_list_reader.open_unread_chat()

    def has_whatsapp_loaded(self) -> bool:
        return self.header_reader.has_whatsapp_loaded()

    def has_open_chat(self) -> bool:
        return self.header_reader.has_open_chat()

    def get_customer_phone(self) -> str:
        return self.header_reader.get_customer_phone()

    def get_last_customer_message(self) -> str:
        return self.open_chat_reader.read_last_customer_message()

    def get_recent_customer_messages(
        self,
        limit: int | None = None,
    ) -> tuple[str, ...]:
        return self.open_chat_reader.read_recent_customer_messages(limit)

    def _candidate_messages_from_unread_chat(
        self,
        snapshot: WhatsAppMessageSnapshot,
    ) -> tuple[ExtractedWhatsAppMessage, ...]:
        unread_count = max(self.chat_list_reader.last_opened_unread_count, 1)
        candidates = (
            snapshot.incoming_after_last_outgoing
            or snapshot.incoming_messages
        )

        return candidates[-unread_count:]

    def _unread_message_scan_limit(self) -> int:
        unread_count = max(self.chat_list_reader.last_opened_unread_count, 1)

        return max(unread_count + 10, self.MESSAGE_SCAN_LIMIT)

    def _filter_new_customer_messages(
        self,
        customer_contact: str,
        messages: tuple[ExtractedWhatsAppMessage, ...],
    ) -> tuple[ExtractedWhatsAppMessage, ...]:
        new_messages: list[ExtractedWhatsAppMessage] = []

        for message in messages:
            if not message.text.strip():
                continue

            if self.message_state.was_sent_by_bot(message.text):
                continue

            if self.message_state.is_processed(customer_contact, message):
                self._status_messages.append(
                    "Mensagem ignorada por já ter sido processada: "
                    f"contato={customer_contact}, "
                    f"id={self.message_state.message_key(customer_contact, message)}, "
                    f'conteúdo="{message.text}"'
                )
                continue

            new_messages.append(message)

        filtered_messages = tuple(new_messages)
        self.message_state.remember_seen(customer_contact, filtered_messages)

        return filtered_messages

    def _to_whatsapp_messages(
        self,
        customer_contact: str,
        messages: tuple[ExtractedWhatsAppMessage, ...],
    ) -> tuple[WhatsAppMessage, ...]:
        return tuple(
            WhatsAppMessage(
                customer_contact=customer_contact,
                content=message.text,
                external_id=self.message_state.message_key(customer_contact, message),
                received_at=message.timestamp or None,
            )
            for message in messages
        )

    def _read_last_customer_message_from_dom(self) -> str:
        return self.message_extractor.read_last_customer_message()

    def _read_recent_customer_messages_from_dom(
        self,
        limit: int | None = None,
        only_after_last_outgoing: bool = False,
    ) -> list[str]:
        return list(
            self.message_extractor.read_recent_customer_messages(
                limit=limit,
                only_after_last_outgoing=only_after_last_outgoing,
            )
        )

    def _build_locator(self, selector_key: str) -> Locator:
        return self.element_finder.build_locator(selector_key)

    def _find_elements(self, locators: LocatorGroup) -> list[WebElement]:
        return self.element_finder.find_visible_elements(locators)

    def _read_element_text(self, element: WebElement) -> str:
        return self.element_finder.read_element_text(element)

    def _wait_for_elements(
        self,
        locators: LocatorGroup,
        timeout: int = 5,
    ) -> list[WebElement]:
        return self.element_finder.wait_for_elements(locators, timeout)

    def _wait_for_text(
        self,
        selector_key: str,
        fallback_locators: LocatorGroup,
    ) -> str:
        return self.element_finder.wait_for_text(selector_key, fallback_locators)

    def _find_message_box(self) -> WebElement | None:
        return self.message_sender._find_message_box()

    def _type_message(self, message_box: WebElement, content: str) -> None:
        self.message_sender._type_message(message_box, content)

    def _type_line(self, message_box: WebElement, line: str) -> None:
        self.message_sender._type_line(message_box, line)

    def _has_non_bmp_character(self, value: str) -> bool:
        return self.message_sender._has_non_bmp_character(value)
