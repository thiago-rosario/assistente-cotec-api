from enum import Enum


class WhatsAppServiceState(str, Enum):
    IDLE = "IDLE"
    READING = "READING"
    PROCESSING = "PROCESSING"
    SENDING = "SENDING"
    RECOVERING = "RECOVERING"
