from application.process_unread_message import ProcessUnreadMessageUseCase
from application.whatsapp_bot import WhatsAppBot
from browser import BrowserFactory
from config.config import Config
from services.editacodigo_service import EditaCodigoService
from services.selenium_error_formatter import SeleniumErrorFormatter
from services.whatsapp_service import WhatsAppService


def main() -> None:
    editacodigo_service = EditaCodigoService()
    selectors = editacodigo_service.get_whatsapp_selectors()

    driver = BrowserFactory.create_chrome_driver(Config.SESSION_FOLDER)
    driver.get(Config.WHATSAPP_URL)

    whatsapp_service = WhatsAppService(driver, selectors)
    process_unread_message = ProcessUnreadMessageUseCase(whatsapp_service)
    bot = WhatsAppBot(
        process_unread_message,
        Config.BOT_INTERVAL_SECONDS,
        error_formatter=SeleniumErrorFormatter(),
    )

    bot.run()


if __name__ == "__main__":
    main()
