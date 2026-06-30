import argparse

from application.process_unread_message import ProcessUnreadMessageUseCase
from application.whatsapp_bot import WhatsAppBot
from browser import BrowserFactory
from config.config import Config
from services.editacodigo_service import EditaCodigoService
from services.php_bridge_command_listener import PhpBridgeCommandListener
from services.php_bridge_message_formatter import PhpBridgeMessageFormatter
from services.selenium_error_formatter import SeleniumErrorFormatter
from services.whatsapp_service import WhatsAppService


def flushed_print(message: str) -> None:
    print(message, flush=True)


def discard_output(message: str) -> None:
    return None


def parse_arguments() -> argparse.Namespace:
    parser = argparse.ArgumentParser()
    parser.add_argument(
        "--bridge-output",
        choices=("text", "json"),
        default="text",
        help="Formato de saída usado pela ponte com o PHP.",
    )

    return parser.parse_args()


def main() -> None:
    arguments = parse_arguments()
    editacodigo_service = EditaCodigoService()
    selectors = editacodigo_service.get_whatsapp_selectors()

    driver = BrowserFactory.create_chrome_driver(Config.SESSION_FOLDER)
    driver.get(Config.WHATSAPP_URL)

    whatsapp_service = WhatsAppService(driver, selectors)
    if arguments.bridge_output == "json":
        PhpBridgeCommandListener(
            whatsapp_service.send_message,
            finish_processing_message=whatsapp_service.finish_processing_message,
            output=discard_output,
        ).start()

    process_unread_message = ProcessUnreadMessageUseCase(whatsapp_service)
    bot = WhatsAppBot(
        process_unread_message,
        Config.BOT_INTERVAL_SECONDS,
        output=flushed_print,
        error_formatter=SeleniumErrorFormatter(),
        message_formatter=PhpBridgeMessageFormatter()
        if arguments.bridge_output == "json"
        else None,
    )

    bot.run()


if __name__ == "__main__":
    main()
