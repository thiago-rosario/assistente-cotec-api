/* eslint-disable */
// This script is injected via Selenium execute_script(), which wraps it in a
// function automatically. The top-level `return` at the end is intentional.
// noinspection JSAnnotator

const normalize = (value) => String(value || '')
    .replace(/\u200e|\u200f/g, '')
    .replace(/\s+/g, ' ')
    .trim();

const options = arguments[0] || {};
const messageLimit = Number(options.messageLimit || 0);

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
        : element.closest('[data-id]') || element.querySelector('[data-id]');

    return dataElement?.getAttribute('data-id') || '';
};

const messagePlainText = (element) => {
    const plainTextElement = element.matches('[data-pre-plain-text]')
        ? element
        : element.querySelector('[data-pre-plain-text]');

    return plainTextElement?.getAttribute('data-pre-plain-text') || '';
};

const messageSender = (element) => {
    const plainText = messagePlainText(element);
    const senderMatch = plainText.match(/\]\s*(.*?):\s*$/);

    return normalize(senderMatch?.[1] || '');
};

const messageTimestamp = (element) => {
    const plainText = messagePlainText(element);
    const timestampMatch = plainText.match(/^\[([^\]]+)\]/);

    return normalize(timestampMatch?.[1] || '');
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
            .flatMap((text) => text.split('\n'))
            .map(normalize)
            .filter(Boolean)
            .filter((text) => ! /^\d{1,2}:\d{2}$/.test(text));

        const uniqueLines = [...new Set(lines)];

        if (uniqueLines.length) {
            return uniqueLines.join('\n').trim();
        }
    }

    const fallbackLines = visibleText(container)
        .split('\n')
        .map(normalize)
        .filter(Boolean)
        .filter((text) => ! /^\d{1,2}:\d{2}$/.test(text));

    return [...new Set(fallbackLines)].join('\n').trim();
};

const messageContainerSelector = (
    'div.message-in, div.message-out, '
    + '[class*="message-in"], [class*="message-out"], '
    + '[data-id]'
);

const messageElements = [
    ...document.querySelectorAll(
        messageContainerSelector + ', [data-pre-plain-text]'
    ),
];
const seenMessageContainers = new Set();
const messageContainers = [];

for (let index = messageElements.length - 1; index >= 0; index -= 1) {
    const element = messageElements[index];
    const messageContainer = element.closest(messageContainerSelector)
        || element.closest('[data-pre-plain-text]') || element;

    if (seenMessageContainers.has(messageContainer)) {
        continue;
    }

    seenMessageContainers.add(messageContainer);
    messageContainers.unshift(messageContainer);

    if (messageLimit > 0 && messageContainers.length >= messageLimit) {
        break;
    }
}

const messages = messageContainers
    .map((element) => {
        const direction = messageDirection(element);
        const text = messageText(element);

        return {
            direction,
            text,
            key: messageDataId(element),
            timestamp: messageTimestamp(element),
        };
    })
    .filter((message) => {
        return (message.direction === 'incoming' || message.direction === 'outgoing')
            && message.text;
    });

let lastOutgoingIndex = -1;

messages.forEach((message, index) => {
    if (message.direction === 'outgoing') {
        lastOutgoingIndex = index;
    }
});

const incomingMessages = messages.filter((message) => {
    return message.direction === 'incoming';
});

const outgoingMessages = messages.filter((message) => {
    return message.direction === 'outgoing';
});

const incomingAfterLastOutgoingMessages = messages.filter((message, index) => {
    return message.direction === 'incoming' && index > lastOutgoingIndex;
});

return {
    messages,
    incomingMessages,
    outgoingMessages,
    incomingAfterLastOutgoingMessages,
    incomingTexts: incomingMessages.map((message) => message.text),
    outgoingTexts: outgoingMessages.map((message) => message.text),
    incomingAfterLastOutgoingTexts: incomingAfterLastOutgoingMessages.map((message) => {
        return message.text;
    }),
    conversationCount: messages.length,
};
