import argparse
import logging
from pathlib import Path

from config.config import Config
from core.bot_runner import BotRunner
from core.driver_factory import DriverFactory
from core.logger_config import configure_logging
from core.message_processor import MessageProcessor
from core.recovery_manager import RecoveryManager
from core.whatsapp_health_checker import WhatsAppHealthChecker
from services.editacodigo_service import EditaCodigoService
from services.php_bridge_command_listener import PhpBridgeCommandListener
from services.php_bridge_message_formatter import PhpBridgeMessageFormatter
from services.whatsapp_message_state import WhatsAppMessageState


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
    configure_logging(logging.INFO)

    arguments = parse_arguments()
    editacodigo_service = EditaCodigoService()
    selectors = editacodigo_service.get_whatsapp_selectors()
    message_state = WhatsAppMessageState(Path(Config.MESSAGE_STATE_PATH).expanduser())
    message_processor = MessageProcessor(
        selectors=selectors,
        message_state=message_state,
        output=flushed_print,
        message_formatter=PhpBridgeMessageFormatter()
        if arguments.bridge_output == "json"
        else None,
    )
    driver_factory = DriverFactory(
        session_folder=Config.SESSION_FOLDER,
        whatsapp_url=Config.WHATSAPP_URL,
    )
    recovery_manager = RecoveryManager(driver_factory)
    health_checker = WhatsAppHealthChecker(message_processor.has_whatsapp_loaded)

    if arguments.bridge_output == "json":
        PhpBridgeCommandListener(
            message_processor.send_message,
            output=discard_output,
        ).start()

    bot = BotRunner(
        driver_factory=driver_factory,
        recovery_manager=recovery_manager,
        message_processor=message_processor,
        health_checker=health_checker,
        interval_seconds=Config.BOT_INTERVAL_SECONDS,
    )

    bot.run()


if __name__ == "__main__":
    main()
