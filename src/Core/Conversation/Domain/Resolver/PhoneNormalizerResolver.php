<?php

declare(strict_types=1);

namespace App\Core\Conversation\Domain\Resolver;

class PhoneNormalizerResolver
{
    public function normalize(?string $contact): ?string
    {
        if ($contact === null) {
            return null;
        }

        $contact = trim($contact);

        if ($contact === '') {
            return null;
        }

        $contact = str_starts_with($contact, 'whatsapp:')
            ? substr($contact, 9)
            : $contact;

        if (preg_match('/^\+?\d[\d\s().-]+$/', $contact) !== 1) {
            return null;
        }

        return preg_replace('/[^\d+]/', '', $contact);
    }
}
