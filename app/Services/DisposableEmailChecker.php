<?php

namespace App\Services;

class DisposableEmailChecker
{
    protected const DISPOSABLE_DOMAINS = [
        '10minutemail.com', '10minutemail.net', '20minutemail.com',
        'anonbox.net', 'anonmail.top', 'anonymbox.com',
        'burnermail.io', 'boximail.com',
        'discard.email', 'disposable.com', 'disposablemail.com', 'dispostable.com',
        'emailondeck.com', 'email-temp.com', 'emailtemporanea.com',
        'fakeinbox.com', 'fakemail.net', 'fakemailgenerator.com',
        'getnada.com', 'getairmail.com', 'guerrillamail.com', 'guerrillamail.net',
        'guerrillamail.org', 'guerrillamail.biz', 'guerrillamail.info', 'guerrillamail.de',
        'harakirimail.com',
        'inboxbear.com', 'inboxkitten.com',
        'jetable.org',
        'mailcatch.com', 'mailinator.com', 'mailinator.net', 'mailinator.org',
        'mailnesia.com', 'mailnull.com', 'mailsac.com', 'maildrop.cc',
        'mintemail.com', 'mohmal.com', 'moakt.com',
        'nowmymail.com',
        'rootfest.net',
        'sharklasers.com', 'spam4.me', 'spamgourmet.com', 'sogetthis.com',
        'spamex.com', 'spambog.com', 'spambox.us', 'spam.la',
        'tempinbox.com', 'tempmail.com', 'tempmail.net', 'tempmail.plus',
        'temp-mail.org', 'temp-mail.io', 'tempmailer.com', 'tempmailo.com',
        'throwawaymail.com', 'tmpmail.org', 'trashmail.com', 'trashmail.net',
        'trashmail.de', 'trashmail.io', 'trash-mail.com',
        'yopmail.com', 'yopmail.net', 'yopmail.fr',
        'zetmail.com',
    ];

    public static function isDisposable(?string $email): bool
    {
        if (empty($email) || !str_contains($email, '@')) {
            return false;
        }

        $domain = strtolower(trim(substr(strrchr($email, '@'), 1)));

        return in_array($domain, self::DISPOSABLE_DOMAINS, true);
    }

    public static function suspicionReason(?string $email): ?string
    {
        if (self::isDisposable($email)) {
            return 'disposable_email_domain';
        }

        return null;
    }
}
