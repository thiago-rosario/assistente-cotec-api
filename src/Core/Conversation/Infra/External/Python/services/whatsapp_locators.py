from selenium.webdriver.common.by import By


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

CUSTOMER_CONTACT_FALLBACK_LOCATORS: LocatorGroup = (
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

CUSTOMER_MESSAGE_FALLBACK_LOCATORS: LocatorGroup = (
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

UNREAD_CHAT_FALLBACK_LOCATORS: LocatorGroup = (
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

MESSAGE_BOX_FALLBACK_LOCATORS: LocatorGroup = (
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

WHATSAPP_LOADED_LOCATORS: LocatorGroup = (
    (
        By.XPATH,
        "//*[@id='pane-side']"
        " | //*[@aria-label='Lista de conversas']"
        " | //*[@aria-label='Chat list']"
        " | //*[@role='grid']",
    ),
)

OPEN_CHAT_LOCATORS: LocatorGroup = (
    (
        By.XPATH,
        "//footer//div[@contenteditable='true']"
        " | //div[@role='textbox' and @contenteditable='true']",
    ),
)
